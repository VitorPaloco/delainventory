# 📦 DelaInventory

DelaInventory is a GLPI plugin designed to improve inventory traceability, audit history, and asset identification through automatic Zebra label printing.

The plugin adds a dedicated tab to GLPI assets, allowing users to register inventory checks, keep a complete audit history, and print custom asset labels for quick access to equipment information.

![Banner](docs/banner_en.png)

> Built using the official GLPI plugin architecture, DelaInventory integrates seamlessly with GLPI assets and uses ZPL (Zebra Programming Language) to print professional asset labels directly to Zebra printers.

## ✨ Features

- Manual inventory registration and asset history.
- Configurable support for multiple GLPI asset types.
- Customizable ZPL labels with dynamic asset variables and QR Codes.
- Direct ZPL printing to Zebra printers over TCP/IP.
- Printer connection testing and ZPL preview through Labelary.

> **Supported assets:** Computers, Monitors, Printers, and Phones.

## 🏷️ Generated Label

DelaInventory allows users to create fully customizable asset labels using ZPL templates configured directly through the plugin interface.

Users can define their own ZPL templates and use the available asset variables to dynamically generate labels according to their organization's needs.

![Tags View](docs/screenshots/preview2.png)

> The label can include any information available through the supported variables

## ⚙️ How It Works

### Configuration

1. Access the DelaInventory configuration page.
2. Select which GLPI asset types should be tracked.
3. Define the printer IP address and TCP port.
4. Create or paste a custom ZPL template.
5. Use the available DelaInventory variables to dynamically insert asset information.
6. Save the configuration.

![Config Page](docs/screenshots/preview1.png)

### Inventory Registration

1. Open a supported asset in GLPI.
2. Open the **DelaInventory** tab.
3. Enter a comment describing the inventory check.
4. Save the inventory record.
5. The record is stored in the asset's inventory history together with the user and creation date.

### Label Printing

1. Open a supported asset in GLPI.
2. Click **Print Label**.
3. The plugin retrieves and validates the asset information.
4. The ZPL label is generated dynamically using the configured template and asset variables.
5. The ZPL is sent directly to the configured Zebra printer via TCP/IP.
6. The label is printed automatically.

![Log Page](docs/screenshots/preview3.png)

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

## 📈 Roadmap

Upcoming improvements planned for future releases:

- Additional translations

- Additional asset types

- Reports and dashboards
  - Provide inventory and printing statistics.
  - Add visual dashboards for monitoring and analysis.
  
- Security and performance improvements
  - Further improve security, performance, and plugin architecture.

## 👨‍💻 Author

Developed by **Vitor Paloco** to improve asset inventory management and traceability within GLPI.