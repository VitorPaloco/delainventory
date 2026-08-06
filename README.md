# 📦 DelaInventory - Inventory Control and Label Printing for GLPI

DelaInventory is a GLPI plugin designed to improve inventory traceability, audit history, and asset identification through automatic Zebra label printing.

The plugin adds a dedicated tab to GLPI assets, allowing users to register inventory checks, keep a complete audit history, and print asset labels containing QR Codes for quick access to equipment information.

> Built using the official GLPI plugin architecture, DelaInventory integrates seamlessly with GLPI assets and uses ZPL (Zebra Programming Language) to print professional asset labels directly to Zebra printers.

<br>

<img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white"/> <img src="https://img.shields.io/badge/GLPI-2C6BED?style=for-the-badge"/> <img src="https://img.shields.io/badge/Zebra_ZPL-000000?style=for-the-badge"/> <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white"/>

## ✨ Features

- Manual inventory registration by users
- Complete inventory history for each asset
- Audit trail with user identification
- Support for multiple GLPI asset types
- Automatic asset label generation
- QR Code linking directly to the asset page in GLPI
- Direct ZPL printing to Zebra printers over TCP/IP
- Native integration with the GLPI interface

## 🖥️ Supported Assets

Currently supported asset types:

- Computers
- Monitors
- Printers
- Phones

The plugin architecture is designed to support additional GLPI asset types in future releases.

## 🏷️ Generated Label

DelaInventory allows users to create fully customizable asset labels using ZPL templates configured directly through the plugin interface. The user defines the ZPL template and uses the available asset variables to dynamically generate labels according to their organization's needs.

The label can include:

- Asset ID
- Asset description
- Serial number
- Assigned location or responsible entity
- QR Code for quick access to the asset in GLPI
- Any other information available through the supported variables

![Tags View](docs/screenshots/preview2.png)

## 📂 Project Structure

```text
delainventory/
├── docs/
│
├── front/
│   ├── action.php
│   ├── config.php
│   ├── log.php
│   └── print.php
│
├── src/
│   ├── Config.php
│   ├── Log.php
│   ├── Printer.php
│   └── ZplVars.php
│
├── templates/
│   └── config.html.twig
│
├── tests/
├── tools/
├── vendor/
│
├── hook.php
├── setup.php
└── plugin.xml
```

The plugin follows the official GLPI plugin architecture, using modern PHP practices with Composer autoloading, Twig templates, and separated source classes for configuration and inventory logs.

## ⚙️ How It Works

### Inventory Registration

1. Access the DelaInventory configuration page.
2. Define the printer IP address and TCP port.
3. Create or paste a custom ZPL template.
4. Use the available DelaInventory variables to dynamically insert asset information.
5. Save the configuration.

### Label Printing

1. Open an asset in GLPI.
2. Click **Print Label**.
3. The plugin retrieves the asset information.
4. A ZPL label is generated dynamically.
5. The ZPL is sent directly to a Zebra printer via TCP/IP (port 9100).
6. The label is printed automatically.

## 🔧 Requirements

- GLPI
- PHP 8+
- MySQL or MariaDB
- Zebra printer compatible with ZPL
- Network connectivity between the GLPI server and the printer

## 🚀 Installation

Clone the repository into your GLPI plugins directory:

```bash
git clone https://github.com/VitorPaloco/delainventory.git
```

Install dependencies:

```bash
cd delainventory
composer install --no-dev
```

Enable the plugin through the GLPI administration panel:

```text
Setup → Plugins → DelaInventory → Install → Enable
```

## 📸 Screenshots

### Inventory Tab

![Inventory Tab](docs/screenshots/preview1.png)

### Asset View

![Asset View](docs/screenshots/preview3.png)

## 📈 Roadmap

Future improvements being evaluated:

- Printer connection validation button
- Label preview before printing

- Multiple ZPL template management
  - Store multiple label templates.
  - Define a default template.
  - Assign different templates according to GLPI entities or organizational needs.

- Reports and dashboards
- Additional GLPI asset types support
- Performance and security improvements

## 👨‍💻 Author

Developed by **Vitor Paloco** to improve asset inventory management and traceability within GLPI.