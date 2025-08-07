import csv

def read_csv(filepath):
    with open(filepath, newline='', encoding='utf-8') as csvfile:
        reader = csv.DictReader(csvfile)
        for row in reader:
            print(row)

if __name__ == "__main__":
    read_csv('dataset.csv')