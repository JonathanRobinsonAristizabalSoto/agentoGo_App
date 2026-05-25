# Backups y restauración - Guía rápida

Pequeña guía de cómo hacer copias de seguridad (DB y storage) y cómo restaurarlas. Pensado para ambientes Linux/Windows, explicado de forma simple.

1) Backup de base de datos

- MySQL / MariaDB (ejemplo):

```bash
mysqldump -u DB_USER -p DB_NAME > /backups/db-$(date +%F).sql
```

- PostgreSQL (ejemplo):

```bash
PGPASSWORD=$DB_PASS pg_dump -U $DB_USER -h $DB_HOST -d $DB_NAME -F c -b -v -f /backups/db-$(date +%F).dump
```

En Windows (PowerShell) con `mysqldump`:

```powershell
& "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe" -u DB_USER -pDB_PASS DB_NAME > C:\backups\db-2026-05-24.sql
```

2) Backup de archivos (`storage`) y uploads

Ejemplo con `tar` (Linux):

```bash
tar -czf /backups/storage-$(date +%F).tar.gz -C /path/to/apps/api/storage app public
```

3) Almacenamiento seguro

- Subir backups a S3 / DigitalOcean Spaces / Azure Blob. Mantener al menos 7-14 días de retención.
- Encriptar backups sensibles antes de subir.

4) Automatizar (Linux)

- Usar cron para ejecutar script diario (`/usr/local/bin/agento_backup.sh`) y rotar con `logrotate` o `find -mtime` para eliminar viejos.

Ejemplo cron (diario a las 02:00):

```cron
0 2 * * * /usr/local/bin/agento_backup.sh
```

5) Automatizar (Windows)

- Usar Task Scheduler para ejecutar un script PowerShell que haga `mysqldump` y suba a S3.

6) Restauración rápida

- MySQL:

```bash
mysql -u DB_USER -p DB_NAME < /backups/db-2026-05-24.sql
```

- PostgreSQL (restore del formato `-F c`):

```bash
pg_restore -U $DB_USER -d $DB_NAME /backups/db-2026-05-24.dump
```

7) Probar restorations

- Tener un ambiente `staging` donde probar la restauración periódicamente.

8) Notas y buenas prácticas (siempre)

- Probar procesos de restore antes de confiar en ellos.
- Mantener backups fuera del mismo datacenter si es posible.
- Registrar y alertar fallos del job de backup.
