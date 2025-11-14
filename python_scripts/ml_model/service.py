from __future__ import annotations

import logging
from pathlib import Path
from typing import Any, Dict, Optional

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
# Changed "validator" to "field_validator"
from pydantic import BaseModel, Field, field_validator

from features import SCHEMA, describe_features
from predict import DEFAULT_MODEL_PATH, ModelNotFoundError, predict


logger = logging.getLogger(__name__)
logging.basicConfig(level=logging.INFO)


class PredictionRequest(BaseModel):
    data: Dict[str, Any] = Field(..., description="Student feature payload")
    model: Optional[str] = Field(
        default=DEFAULT_MODEL_PATH.name,
        description="Optional model file name within the models directory",
    )

    # Changed decorator from @validator to @field_validator
    @field_validator("model")
    def validate_model_name(cls, value: str) -> str:
        if value and "/" in value:
            raise ValueError("Model name must not contain directory separators")
        return value


class PredictionResponse(BaseModel):
    prediction: str
    confidence: float
    probabilities: Dict[str, float]
    feature_importance: list[Dict[str, Any]]
    model_metadata: Dict[str, Any]


MODELS_DIR = Path(DEFAULT_MODEL_PATH).parent

app = FastAPI(
    title="Dropout Prediction Service",
    version="1.0.0",
    description="FastAPI service hosting dropout prediction models",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.get("/health", tags=["system"])
async def health_check() -> Dict[str, str]:
    return {"status": "ok"}


@app.get("/schema", tags=["system"])
async def feature_schema() -> Dict[str, Any]:
    return {"features": describe_features(), "target": SCHEMA.get("target", {})}


@app.post("/predict", response_model=PredictionResponse, tags=["prediction"])
async def make_prediction(request: PredictionRequest) -> PredictionResponse:
    model_path = MODELS_DIR / request.model
    try:
        result = predict(request.data, model_path=model_path)
    except ModelNotFoundError as exc:
        logger.error("Model not found: %s", exc)
        raise HTTPException(status_code=404, detail=str(exc))
    except ValueError as exc:
        logger.exception("Invalid prediction payload")
        raise HTTPException(status_code=400, detail=str(exc))
    except Exception as exc:  # pragma: no cover
        logger.exception("Unexpected prediction error")
        raise HTTPException(status_code=500, detail="Prediction failed") from exc

    return PredictionResponse(**result)


@app.get("/models", tags=["system"])
async def list_models() -> Dict[str, Any]:
    models = [
        {
            "name": path.name,
            "size_bytes": path.stat().st_size,
            "modified_at": path.stat().st_mtime,
        }
        for path in MODELS_DIR.glob("*.joblib")
    ]
    return {"models": models}


if __name__ == "__main__":
    import uvicorn

    uvicorn.run("service:app", host="0.0.0.0", port=8000, reload=False)