# Login Tracker Block for Moodle

An intuitive, lightweight Moodle block plugin that tracks and visualizes user login frequencies. It provides users with an aesthetic bar chart dashboard displaying personal login statistics broken down by month (Year View) or by specific days (Month View with drill-down capability).

## 🚀 Features

* **Visual Analytics:** Renders clean, modern bar charts natively using Moodle's core Chart API.
* **Dual-View Navigation:** * **Year View:** Displays total login frequencies for each of the 12 months of a selected year. Includes clickable badge buttons to inspect specific months.
  * **Month View:** Drills down to display daily login statistics for the chosen month with a simple one-click "Back to Year View" button.
* **Cross-DB Safe:** Built with performance-conscious timestamp boundaries, eliminating database-specific string formatting functions for universal compatibility (MySQL, MariaDB, PostgreSQL, MSSQL).

---

## 📂 Directory Structure

For Moodle to recognize the block correctly, ensure your folder structure matches this setup perfectly:

```text
login_tracker/
├── db/
│   └── access.php                  # Capabilities and block permissions
├── lang/
│   └── en/
│       └── block_login_tracker.php # English localization strings
├── block_login_tracker.php         # Main block class and query logic
├── version.php                     # Component version and dependency metadata
└── README.md                       # Documentation

