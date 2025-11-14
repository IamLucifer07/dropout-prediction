from __future__ import annotations

import json
from pathlib import Path
from typing import Any, Dict, Optional

import joblib
import pandas as pd

from features import SCHEMA, ensure_feature_order


BASE_DIR = Path(__file__).resolve().parent
DEFAULT_MODEL_PATH = BASE_DIR / "models" / "random_forest.joblib"


class ModelNotFoundError(FileNotFoundError):
    pass


def _load_model(model_path: Path) -> Dict[str, Any]:
    if not model_path.exists():
        raise ModelNotFoundError(f"Model not found at {model_path}")
    return joblib.load(model_path)


def predict(input_data: Dict[str, Any], model_path: Path | str = DEFAULT_MODEL_PATH) -> Dict[str, Any]:
    model_path = Path(model_path)
    model_payload = _load_model(model_path)

    pipeline = model_payload["model"]
    label_encoder = model_payload["label_encoder"]
    feature_names = model_payload["feature_names"]
    feature_importances = model_payload.get("feature_importances", {})

    normalized = ensure_feature_order(input_data)
    input_df = pd.DataFrame([normalized], columns=feature_names)

    predictions = pipeline.predict(input_df)
    probabilities = pipeline.predict_proba(input_df)[0]

    predicted_index = int(predictions[0])
    predicted_label = label_encoder.inverse_transform([predicted_index])[0]
    probability = float(probabilities[predicted_index])

    formatted_importances = [
        {"feature": name, "importance": float(value)}
        for name, value in sorted(feature_importances.items(), key=lambda item: item[1], reverse=True)[:5]
    ]

    return {
        "prediction": predicted_label,
        "confidence": probability,
        "probabilities": {
            str(label): float(probabilities[idx])
            for idx, label in enumerate(label_encoder.classes_)
        },
        "feature_importance": formatted_importances,
        "model_metadata": {
            "model_path": str(model_path.name),
            "feature_schema_version": SCHEMA,
            "available_classes": label_encoder.classes_.tolist(),
        },
    }


if __name__ == "__main__":
    import sys

    if len(sys.argv) < 2:
        raise SystemExit("Usage: python predict.py '{\"age\": 20, ...}'")

    input_json = sys.argv[1]
    input_payload = json.loads(input_json)
    result = predict(input_payload)
    print(json.dumps(result, indent=2))
