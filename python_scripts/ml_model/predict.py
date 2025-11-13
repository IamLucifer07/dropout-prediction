# python_scripts/ml_model/predict.py
import pandas as pd
import joblib
import sys
import json

def predict(input_data, model_path='models/random_forest.joblib'):
    # Load model and feature names
    model_data = joblib.load(model_path)
    model = model_data['model']
    feature_names = model_data['feature_names']
    
    # Convert input to DataFrame with correct feature order
    input_df = pd.DataFrame([input_data])[feature_names]
    
    # Predict
    prediction = model.predict(input_df)
    probabilities = model.predict_proba(input_df)
    
    return {
        'prediction': int(prediction[0]),
        'probability': float(probabilities[0][1]),  # Probability of positive class
        'feature_names': feature_names
    }

if __name__ == "__main__":
    # For command-line testing
    input_json = sys.argv[1]
    input_data = json.loads(input_json)
    result = predict(input_data)
    print(json.dumps(result))