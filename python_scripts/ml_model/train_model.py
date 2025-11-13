import os
import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.tree import DecisionTreeClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score, classification_report
import joblib
from preprocess import preprocess_data

def train_and_save_models():
    os.makedirs('models', exist_ok=True)
    
    # Preprocess data
    df = preprocess_data(
        input_path='../../python_scripts/dataset.csv',
        output_path='../../python_scripts/processed_data.csv'
    )
    
    # Split data
    X = df.drop(['Target'], axis=1)  # Adjust 'Target' to your actual target column
    y = df['Target']
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    
    # Train models
    print("Training Random Forest...")
    rf = RandomForestClassifier(n_estimators=100, random_state=42)
    rf.fit(X_train, y_train)
    
    print("Training Decision Tree...")
    dt = DecisionTreeClassifier(random_state=42)
    dt.fit(X_train, y_train)
    
    # Evaluate
    print("\nModel Evaluation:")
    print("Random Forest Accuracy:", accuracy_score(y_test, rf.predict(X_test)))
    print("Random Forest Report:\n", classification_report(y_test, rf.predict(X_test)))
    
    print("\nDecision Tree Accuracy:", accuracy_score(y_test, dt.predict(X_test)))
    print("Decision Tree Report:\n", classification_report(y_test, dt.predict(X_test)))
    
    # Save models
    print("\nSaving models...")
    joblib.dump({
        'model': rf,
        'feature_names': list(X.columns),
        'classes': rf.classes_
    }, 'models/random_forest.joblib')
    
    joblib.dump({
        'model': dt,
        'feature_names': list(X.columns),
        'classes': dt.classes_
    }, 'models/decision_tree.joblib')
    
    print("Models saved successfully in 'models/' directory")

if __name__ == "__main__":
    train_and_save_models()