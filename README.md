# pohoda-csas

Import CSAS Bank statements and movements to Stormware Pohoda

## Purpose

This application downloads bank statements in a machine-readable format from the API of Česká spořitelna using the spojenet/csas-accountsapi library. Individual transactions from these statements are then imported into the Pohoda accounting system using the vitexsoftware/pohoda-connector library.

## Setup

1. Install dependencies using Composer:

   .. code-block:: bash

      composer install

2. Configure environment variables for API access and Pohoda connection. Example variables:

   - CSAS_API_KEY
   - CSAS_ACCESS_TOKEN
   - ACCOUNT_NUMBER (UUID or IBAN)
   - POHODA_URL
   - POHODA_USERNAME
   - POHODA_PASSWORD
   - POHODA_ICO
   - CERT_FILE
   - CERT_PASS
   - XIBMCLIENTID

3. Run the application:

   .. code-block:: bash

      php src/csas2pohoda.php

## Contributing

Contributions are welcome! Please submit a pull request or open an issue for any enhancements or bug fixes.

## License

This project is licensed under the MIT License. See the LICENSE file for more details.
