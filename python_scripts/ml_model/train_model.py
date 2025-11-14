from __future__ import annotations

import json
import logging
from pathlib import Path
from typing import Dict

import joblib
import numpy as np
import pandas as pd
from sklearn.compose import ColumnTransformer
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, classification_report, f1_score, precision_score, recall_score
from sklearn.model_selection import train_test_split
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import LabelEncoder, OneHotEncoder, StandardScaler
from sklearn.tree import DecisionTreeClassifier

from features import (
    SCHEMA,
    aggregate_feature_importances,
    get_binary_feature_names,
    get_categorical_feature_names,
    get_feature_names,
    get_numeric_feature_names,
    serialize_feature_schema,
)
from preprocess import CANONICAL_TARGET_COLUMN, preprocess_data


BASE_DIR = Path(__file__).resolve().parent
MODELS_DIR = BASE_DIR / "models"
PROCESSED_DATA_PATH = BASE_DIR.parent / "processed_data.csv"
LOG_PATH = MODELS_DIR / "training.log"
METRICS_PATH = MODELS_DIR / "model_metrics.json"

logger = logging.getLogger(__name__)


def _configure_logging() -> None:
    MODELS_DIR.mkdir(parents=True, exist_ok=True)
    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s [%(levelname)s] %(name)s - %(message)s",
        handlers=[
            logging.StreamHandler(),
            logging.FileHandler(LOG_PATH, mode="w", encoding="utf-8"),
        ],
    )


def _build_preprocessor() -> ColumnTransformer:
    numeric_features = get_numeric_feature_names()
    categorical_features = get_categorical_feature_names()
    binary_features = get_binary_feature_names()

    transformers = []

    if numeric_features:
        transformers.append(("num", Pipeline(steps=[("scaler", StandardScaler())]), numeric_features))

    if categorical_features:
        transformers.append(
            (
                "cat",
                Pipeline(
                    steps=[
                        (
                            "encoder",
                            OneHotEncoder(
                                handle_unknown="ignore", sparse_output=False, dtype=np.float32
                            ),
                        )
                    ]
                ),
                categorical_features,
            )
        )

    if binary_features:
        transformers.append(("bin", "passthrough", binary_features))

    preprocessor = ColumnTransformer(transformers=transformers, remainder="drop")
    return preprocessor


def _build_pipeline(estimator) -> Pipeline:
    preprocessor = _build_preprocessor()
    pipeline = Pipeline(steps=[("preprocessor", preprocessor), ("classifier", estimator)])
    return pipeline


def _train_model(
    pipeline: Pipeline, X_train: pd.DataFrame, y_train: np.ndarray
) -> Pipeline:
    pipeline.fit(X_train, y_train)
    return pipeline


def _evaluate_model(
    pipeline: Pipeline, X_test: pd.DataFrame, y_test: np.ndarray, label_encoder
) -> Dict[str, float]:
    y_pred = pipeline.predict(X_test)
    metrics = {
        "accuracy": float(accuracy_score(y_test, y_pred)),
        "macro_precision": float(precision_score(y_test, y_pred, average="macro", zero_division=0)),
        "macro_recall": float(recall_score(y_test, y_pred, average="macro", zero_division=0)),
        "macro_f1": float(f1_score(y_test, y_pred, average="macro", zero_division=0)),
        "classification_report": classification_report(
            y_test,
            y_pred,
            target_names=label_encoder.classes_,
            zero_division=0,
            output_dict=True,
        ),
    }
    return metrics


def _export_model(
    name: str,
    pipeline: Pipeline,
    label_encoder,
    feature_importances: Dict[str, float],
    metrics: Dict[str, float],
) -> None:
    model_payload = {
        "model": pipeline,
        "label_encoder": label_encoder,
        "feature_names": get_feature_names(),
        "feature_schema": SCHEMA,
        "feature_importances": feature_importances,
        "metrics": metrics,
        "target_categories": label_encoder.classes_.tolist(),
    }

    output_path = MODELS_DIR / f"{name}.joblib"
    joblib.dump(model_payload, output_path)
    logger.info("Saved %s model to %s", name, output_path)


def _json_default(obj):
    if isinstance(obj, (np.floating, np.integer)):
        return obj.item()
    if isinstance(obj, np.ndarray):
        return obj.tolist()
    raise TypeError(f"Object of type {type(obj).__name__} is not JSON serializable")


def _save_metrics(all_metrics: Dict[str, Dict[str, float]]) -> None:
    with METRICS_PATH.open("w", encoding="utf-8") as fp:
        json.dump(all_metrics, fp, indent=2, default=_json_default)
    logger.info("Wrote training metrics to %s", METRICS_PATH)


def train_and_save_models() -> None:
    _configure_logging()

    logger.info("Starting preprocessing pipeline")
    processed_df = preprocess_data()

    X = processed_df.drop(columns=[CANONICAL_TARGET_COLUMN])
    y = processed_df[CANONICAL_TARGET_COLUMN].values

    from sklearn.preprocessing import LabelEncoder

    label_encoder = LabelEncoder()
    y_encoded = label_encoder.fit_transform(y)

    X_train, X_test, y_train, y_test = train_test_split(
        X, y_encoded, test_size=0.2, random_state=42, stratify=y_encoded
    )

    models = {
        "random_forest": RandomForestClassifier(
            n_estimators=300, max_depth=None, min_samples_split=2, random_state=42
        ),
        "decision_tree": DecisionTreeClassifier(
            max_depth=12, min_samples_split=25, random_state=42
        ),
    }

    all_metrics: Dict[str, Dict[str, float]] = {}

    serialize_feature_schema(MODELS_DIR / "feature_schema.json")

    for name, estimator in models.items():
        logger.info("Training %s model", name)
        pipeline = _build_pipeline(estimator)
        trained_pipeline = _train_model(pipeline, X_train, y_train)

        logger.info("Evaluating %s model", name)
        metrics = _evaluate_model(trained_pipeline, X_test, y_test, label_encoder)
        all_metrics[name] = metrics

        preprocessor = trained_pipeline.named_steps["preprocessor"]
        classifier = trained_pipeline.named_steps["classifier"]
        feature_importances: Dict[str, float] = {}

        if hasattr(classifier, "feature_importances_"):
            transformed_names = preprocessor.get_feature_names_out()
            aggregated = aggregate_feature_importances(
                transformed_names, classifier.feature_importances_
            )
            feature_importances = dict(aggregated)

        _export_model(name, trained_pipeline, label_encoder, feature_importances, metrics)

    _save_metrics(all_metrics)
    logger.info("Training complete")


if __name__ == "__main__":
    train_and_save_models()
