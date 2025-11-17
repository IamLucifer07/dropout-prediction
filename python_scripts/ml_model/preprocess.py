from __future__ import annotations

import logging
from pathlib import Path
from typing import Dict, Tuple

import numpy as np
import pandas as pd

from features import ensure_feature_order, serialize_feature_schema


BASE_DIR = Path(__file__).resolve().parent
DEFAULT_INPUT_PATH = BASE_DIR.parent / "dataset.csv"
DEFAULT_OUTPUT_PATH = BASE_DIR.parent / "processed_data.csv"

TARGET_COLUMN = "Target"
CANONICAL_TARGET_COLUMN = "target"

logger = logging.getLogger(__name__)


def _map_gender(value: float | int | str) -> str:
    try:
        numeric = int(float(value))
    except (TypeError, ValueError):
        return "other"

    return "male" if numeric == 1 else "female"


def _map_parental_education(value: float | int | str) -> str:
    try:
        numeric = int(float(value))
    except (TypeError, ValueError):
        return "other"

    if numeric <= 1:
        return "no_education"
    if numeric <= 3:
        return "primary"
    if numeric <= 5:
        return "secondary"
    if numeric <= 8:
        return "bachelors"
    if numeric <= 12:
        return "masters"
    return "phd"


def _map_course(value: float | int | str) -> str:
    text = str(value).strip()
    if not text:
        return "course_unknown"
    return f"course_{text.lower()}"


def _map_living_situation(displaced: float, international: float) -> str:
    if displaced and displaced > 0:
        return "hostel"
    if international and international > 0:
        return "rented_accommodation"
    return "with_family"


def _map_transport(daytime_evening_attendance: float) -> str:
    # 1 -> daytime, 0 -> evening (as per dataset description)
    if daytime_evening_attendance == 1:
        return "bus"
    return "other"


def _map_mental_health(debtor: float, tuition_fees_up_to_date: float) -> float:
    # Simple heuristic: financial stress impacts mental health
    base = 6.0
    if debtor and debtor > 0:
        base -= 1.5
    if tuition_fees_up_to_date and tuition_fees_up_to_date == 1:
        base += 0.5
    return float(np.clip(base, 0, 10))


def _derive_gpa(row: pd.Series) -> float:
    grade_columns = [
        "Curricular units 1st sem (grade)",
        "Curricular units 2nd sem (grade)",
    ]
    grades = row[grade_columns].replace(0, np.nan)
    if grades.dropna().empty:
        return 2.5

    average = grades.dropna().mean()
    gpa = float(np.clip(average / 5.0, 0, 4))
    return round(gpa, 3)


def _derive_attendance(row: pd.Series) -> float:
    enrolled = (
        row["Curricular units 1st sem (enrolled)"] + row["Curricular units 2nd sem (enrolled)"]
    )
    evaluations = (
        row["Curricular units 1st sem (evaluations)"]
        + row["Curricular units 2nd sem (evaluations)"]
    )
    if enrolled == 0:
        return 75.0
    attendance = float((evaluations / enrolled) * 100)
    return float(np.clip(attendance, 0, 100))


def _derive_previous_failures(row: pd.Series) -> float:
    without_evaluations = (
        row["Curricular units 1st sem (without evaluations)"]
        + row["Curricular units 2nd sem (without evaluations)"]
    )
    failed = (
        row["Curricular units 1st sem (enrolled)"]
        - row["Curricular units 1st sem (approved)"]
        + row["Curricular units 2nd sem (enrolled)"]
        - row["Curricular units 2nd sem (approved)"]
    )
    total = float(without_evaluations + failed)
    return float(np.clip(total, 0, 30))


def _derive_study_hours(row: pd.Series) -> float:
    evaluations = (
        row["Curricular units 1st sem (evaluations)"]
        + row["Curricular units 2nd sem (evaluations)"]
    )
    hours = float(np.clip(evaluations * 1.5, 0, 80))
    return round(hours, 2)


def _derive_family_income(scholarship_holder: float, tuition_fees_up_to_date: float) -> float:
    if scholarship_holder and scholarship_holder == 1:
        return 15000.0
    if tuition_fees_up_to_date and tuition_fees_up_to_date == 1:
        return 32000.0
    return 22000.0


def _derive_internet_access(tuition_fees_up_to_date: float, scholarship_holder: float) -> bool:
    return bool((tuition_fees_up_to_date and tuition_fees_up_to_date == 1) or scholarship_holder)


def _derive_extracurricular(daytime_evening_attendance: float) -> bool:
    return bool(daytime_evening_attendance == 1)


def _derive_part_time_job(debtor: float) -> bool:
    return bool(debtor == 1)


def _derive_financial_aid(scholarship_holder: float) -> bool:
    return bool(scholarship_holder == 1)


def _derive_distance_from_home(displaced: float, international: float) -> float:
    if international and international == 1:
        return 500.0
    if displaced and displaced == 1:
        return 75.0
    return 15.0


def _derive_semester(row: pd.Series) -> int:
    enrolled_first = row["Curricular units 1st sem (enrolled)"]
    enrolled_second = row["Curricular units 2nd sem (enrolled)"]
    if enrolled_second > enrolled_first:
        return 2
    return 1


def _derive_grades_average(row: pd.Series) -> float:
    grades = (
        row["Curricular units 1st sem (grade)"] + row["Curricular units 2nd sem (grade)"]
    ) / 2
    return float(np.clip(grades * 5, 0, 100))


def _derive_grades_count(row: pd.Series) -> float:
    count = (
        row["Curricular units 1st sem (enrolled)"] + row["Curricular units 2nd sem (enrolled)"]
    )
    return float(np.clip(count, 0, 40))


def _map_target(raw_target: str) -> str:
    value = str(raw_target).strip().lower()
    if value == "dropout":
        return "dropout"
    if value == "enrolled":
        return "at_risk"
    if value == "graduate":
        return "safe"
    return "safe"


def transform_dataset(raw_df: pd.DataFrame) -> pd.DataFrame:
    feature_records = []

    for _, row in raw_df.iterrows():
        record: Dict[str, object] = {
            "age": float(np.clip(row["Age at enrollment"], 16, 65)),
            "gender": _map_gender(row["Gender"]),
            "gpa": _derive_gpa(row),
            "attendance_rate": _derive_attendance(row),
            "previous_failures": _derive_previous_failures(row),
            "study_hours_per_week": _derive_study_hours(row),
            "internet_access": _derive_internet_access(
                row["Tuition fees up to date"], row["Scholarship holder"]
            ),
            "extracurricular_involvement": _derive_extracurricular(
                row["Daytime/evening attendance"]
            ),
            "part_time_job": _derive_part_time_job(row["Debtor"]),
            "financial_aid": _derive_financial_aid(row["Scholarship holder"]),
            "family_income": _derive_family_income(
                row["Scholarship holder"], row["Tuition fees up to date"]
            ),
            "parental_education_level": _map_parental_education(row["Mother's qualification"]),
            "course_of_study": _map_course(row["Course"]),
            "semester": _derive_semester(row),
            "living_situation": _map_living_situation(row["Displaced"], row["International"]),
            "distance_from_home": _derive_distance_from_home(row["Displaced"], row["International"]),
            "mental_health_score": _map_mental_health(row["Debtor"], row["Tuition fees up to date"]),
            "mode_of_transport": _map_transport(row["Daytime/evening attendance"]),
            "grades_average": _derive_grades_average(row),
            "grades_count": _derive_grades_count(row),
        }

        normalized = ensure_feature_order(record)
        normalized[CANONICAL_TARGET_COLUMN] = _map_target(row[TARGET_COLUMN])
        feature_records.append(normalized)

    processed_df = pd.DataFrame(feature_records)
    return processed_df


def preprocess_data(
    input_path: str | Path = DEFAULT_INPUT_PATH, output_path: str | Path = DEFAULT_OUTPUT_PATH
) -> pd.DataFrame:
    input_path = Path(input_path)
    output_path = Path(output_path)

    if not input_path.exists():
        raise FileNotFoundError(f"Dataset not found at {input_path}")

    logger.info("Loading dataset from %s", input_path)
    raw_df = pd.read_csv(input_path)

    logger.info("Transforming dataset into feature space")
    processed_df = transform_dataset(raw_df)

    logger.info("Writing processed dataset to %s", output_path)
    output_path.parent.mkdir(parents=True, exist_ok=True)
    processed_df.to_csv(output_path, index=False)

    schema_output = output_path.parent / "feature_schema_snapshot.json"
    serialize_feature_schema(schema_output)

    logger.info("Processed dataset shape: rows=%s cols=%s", *processed_df.shape)
    return processed_df


if __name__ == "__main__":
    logging.basicConfig(level=logging.INFO)
    preprocess_data()
