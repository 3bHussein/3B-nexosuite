# Developer Handover

## Stack

- PHP
- MySQL/MariaDB
- Bootstrap admin UI
- Installer-generated templates

## Extension Strategy

Add new modules through:

1. Database tables in installer schema.
2. Helper functions in `includes/functions.php`.
3. Admin pages under `/admin/erp/`.
4. Permissions in catalog and permission labels.
5. Settings and document sequences.

## Safety Notes

Always run PHP lint and schema checker after changes.