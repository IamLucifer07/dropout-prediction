# python_scripts/ml_model/preprocess.py
import pandas as pd
from sklearn.preprocessing import LabelEncoder

def preprocess_data(input_path, output_path):
    # Load data
    df = pd.read_csv(input_path)
    
    # Example preprocessing - adjust based on your dataset
    # Handle missing values
    df = df.dropna()
    
    # Encode categorical variables
    categorical_cols = df.select_dtypes(include=['object']).columns
    for col in categorical_cols:
        le = LabelEncoder()
        df[col] = le.fit_transform(df[col])
    
    # Save processed data
    df.to_csv(output_path, index=False)
    return df

if __name__ == "__main__":
    preprocess_data(
        input_path='../../python_scripts/dataset.csv',
        output_path='../../python_scripts/processed_data.csv'
    )