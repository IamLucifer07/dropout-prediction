import json
from dataclasses import dataclass
from pathlib import Path
from typing import Any, Dict, Iterable, List, Tuple


BASE_DIR = Path(__file__).resolve().parent
SCHEMA_PATH = BASE_DIR / "feature_schema.json"


class FeatureSchemaError(Exception):
    """Raised when feature schema is missing or malformed."""


def _load_schema() -> Dict[str, Any]:
    if not SCHEMA_PATH.exists():
        raise FeatureSchemaError(f"Feature schema not found at {SCHEMA_PATH}")

    with SCHEMA_PATH.open("r", encoding="utf-8") as fp:
        data = json.load(fp)

    if "features" not in data:
        raise FeatureSchemaError("Schema missing 'features' definition")

    return data


SCHEMA = _load_schema()
FEATURE_DEFINITIONS: List[Dict[str, Any]] = SCHEMA["features"]
TARGET_SCHEMA: Dict[str, Any] = SCHEMA.get("target", {"name": "target"})


@dataclass(frozen=True)
class Feature:
    name: str
    type: str
    default: Any
    categories: Tuple[str, ...] = ()
    minimum: float | None = None
    maximum: float | None = None

    @staticmethod
    def from_dict(value: Dict[str, Any]) -> "Feature":
        return Feature(
            name=value["name"],
            type=value["type"],
            default=value.get("default"),
            categories=tuple(value.get("categories", [])),
            minimum=value.get("min"),
            maximum=value.get("max"),
        )

    def normalize(self, raw: Any) -> Any:
        if raw is None or (isinstance(raw, str) and raw.strip() == ""):
            raw = self.default

        if self.type == "numeric":
            try:
                value = float(raw)
            except (TypeError, ValueError):
                value = float(self.default or 0)

            if self.minimum is not None:
                value = max(self.minimum, value)
            if self.maximum is not None:
                value = min(self.maximum, value)

            return round(value, 4)

        if self.type == "binary":
            if isinstance(raw, str):
                lowered = raw.strip().lower()
                if lowered in {"true", "1", "yes", "y"}:
                    return True
                if lowered in {"false", "0", "no", "n"}:
                    return False
            return bool(raw)

        if self.type == "categorical":
            value = str(raw).strip().lower()
            if self.categories and value not in self.categories:
                return str(self.default)
            return value or str(self.default)

        # Fallback: return as-is
        return raw


FEATURES: Tuple[Feature, ...] = tuple(Feature.from_dict(item) for item in FEATURE_DEFINITIONS)
FEATURE_NAME_INDEX: Dict[str, Feature] = {feature.name: feature for feature in FEATURES}


def get_feature_names() -> List[str]:
    return [feature.name for feature in FEATURES]


def get_numeric_feature_names() -> List[str]:
    return [feature.name for feature in FEATURES if feature.type == "numeric"]


def get_binary_feature_names() -> List[str]:
    return [feature.name for feature in FEATURES if feature.type == "binary"]


def get_categorical_feature_names() -> List[str]:
    return [feature.name for feature in FEATURES if feature.type == "categorical"]


def normalize_input(data: Dict[str, Any]) -> Dict[str, Any]:
    normalized: Dict[str, Any] = {}
    for feature in FEATURES:
        value = data.get(feature.name, feature.default)
        normalized_value = feature.normalize(value)
        normalized[feature.name] = normalized_value
    return normalized


def ensure_feature_order(data: Dict[str, Any]) -> Dict[str, Any]:
    normalized = normalize_input(data)
    return {feature.name: normalized[feature.name] for feature in FEATURES}


def serialize_feature_schema(output_path: Path | str) -> None:
    path = Path(output_path)
    path.parent.mkdir(parents=True, exist_ok=True)

    with path.open("w", encoding="utf-8") as fp:
        json.dump(SCHEMA, fp, indent=2)


def describe_features() -> List[Dict[str, Any]]:
    return [
        {
            "name": feature.name,
            "type": feature.type,
            "default": feature.default,
            "categories": list(feature.categories),
            "min": feature.minimum,
            "max": feature.maximum,
        }
        for feature in FEATURES
    ]


def target_categories() -> List[str]:
    return TARGET_SCHEMA.get("categories", [])


def validate_feature_payload(payload: Dict[str, Any]) -> Tuple[bool, List[str]]:
    errors: List[str] = []

    for feature in FEATURES:
        if feature.name not in payload:
            errors.append(f"Missing feature '{feature.name}'")
            continue

        value = payload[feature.name]

        if feature.type == "numeric":
            try:
                float(value)
            except (TypeError, ValueError):
                errors.append(f"Feature '{feature.name}' must be numeric")
                continue

            if feature.minimum is not None and float(value) < feature.minimum:
                errors.append(f"Feature '{feature.name}' must be >= {feature.minimum}")
            if feature.maximum is not None and float(value) > feature.maximum:
                errors.append(f"Feature '{feature.name}' must be <= {feature.maximum}")

        if feature.type == "categorical" and feature.categories:
            value_lower = str(value).strip().lower()
            if value_lower not in feature.categories:
                errors.append(
                    f"Feature '{feature.name}' must be one of {', '.join(feature.categories)}"
                )

    return len(errors) == 0, errors


def aggregate_feature_importances(
    transformed_feature_names: Iterable[str], importances: Iterable[float]
) -> List[Tuple[str, float]]:
    importance_map: Dict[str, float] = {}

    for name, importance in zip(transformed_feature_names, importances):
        base_name = name.split("__", maxsplit=1)[0]
        importance_map[base_name] = importance_map.get(base_name, 0.0) + float(importance)

    sorted_items = sorted(importance_map.items(), key=lambda item: item[1], reverse=True)
    return sorted_items

