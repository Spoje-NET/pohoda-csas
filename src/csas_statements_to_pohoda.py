"""
Script to download CSAS bank statements using csas-statement-tools
and import them into Pohoda using pohoda-abo-importer.

Requirements:
- Python 3.10+
- csas-statement-tools (CLI or API)
- pohoda-abo-importer (CLI or API)

Environment variables required:
    CSAS_API_KEY
    CSAS_ACCESS_TOKEN
    ACCOUNT_NUMBER
    POHODA_URL
    POHODA_USERNAME
    POHODA_PASSWORD
    POHODA_ICO

Author: Vítězslav Dvořák <info@vitexsoftware.cz>
"""

import os
import subprocess
import sys
from typing import List

CSAS_STATEMENT_TOOL_CMD = "csas-statement-tools"
POHODA_ABO_IMPORTER_CMD = "pohoda-abo-importer"
STATEMENT_DIR = "statements"
STATEMENT_FILE_PATTERN = "statement_*.abo"

def get_env_variable(name: str) -> str:
    """
    Retrieve an environment variable or raise an error if not set.

    :param name: Name of the environment variable.
    :return: Value of the environment variable.
    :raises RuntimeError: If the variable is not set.
    """
    value = os.getenv(name)
    if not value:
        raise RuntimeError(f"Environment variable '{name}' is required.")
    return value

def download_statements(output_dir: str) -> None:
    """
    Download CSAS statements using csas-statement-tools CLI.

    :param output_dir: Directory to save downloaded statements.
    :raises RuntimeError: If the download fails.
    """
    api_key = get_env_variable("CSAS_API_KEY")
    access_token = get_env_variable("CSAS_ACCESS_TOKEN")
    account_number = get_env_variable("ACCOUNT_NUMBER")

    os.makedirs(output_dir, exist_ok=True)

    cmd = [
        CSAS_STATEMENT_TOOL_CMD,
        "download",
        "--api-key", api_key,
        "--access-token", access_token,
        "--account", account_number,
        "--output", output_dir
    ]
    try:
        subprocess.run(cmd, check=True)
    except subprocess.CalledProcessError as exc:
        raise RuntimeError(f"Failed to download statements: {exc}")

def find_statement_files(directory: str) -> List[str]:
    """
    Find all statement files in the given directory.

    :param directory: Directory to search for statement files.
    :return: List of statement file paths.
    """
    import glob
    pattern = os.path.join(directory, STATEMENT_FILE_PATTERN)
    return glob.glob(pattern)

def import_statement_to_pohoda(statement_file: str) -> None:
    """
    Import a statement file into Pohoda using pohoda-abo-importer CLI.

    :param statement_file: Path to the statement file.
    :raises RuntimeError: If the import fails.
    """
    pohoda_url = get_env_variable("POHODA_URL")
    pohoda_username = get_env_variable("POHODA_USERNAME")
    pohoda_password = get_env_variable("POHODA_PASSWORD")
    pohoda_ico = get_env_variable("POHODA_ICO")

    cmd = [
        POHODA_ABO_IMPORTER_CMD,
        "import",
        "--url", pohoda_url,
        "--username", pohoda_username,
        "--password", pohoda_password,
        "--ico", pohoda_ico,
        "--file", statement_file
    ]
    try:
        subprocess.run(cmd, check=True)
        print(f"Statement {os.path.basename(statement_file)} imported successfully.")
    except subprocess.CalledProcessError as exc:
        print(f"Failed to import statement {os.path.basename(statement_file)}: {exc}", file=sys.stderr)

def main() -> None:
    """
    Main function to download CSAS statements and import them into Pohoda.
    """
    try:
        download_statements(STATEMENT_DIR)
        statement_files = find_statement_files(STATEMENT_DIR)
        if not statement_files:
            print("No statement files found for import.", file=sys.stderr)
            sys.exit(1)
        for statement_file in statement_files:
            import_statement_to_pohoda(statement_file)
    except Exception as exc:
        print(f"Error: {exc}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    main()