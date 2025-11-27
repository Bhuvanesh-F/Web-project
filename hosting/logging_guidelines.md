# Logging guidelines
- Log failed logins with timestamp, IP, user-agent (do NOT log passwords)
- Log admin actions and CRUD on sensitive records
- Log suspicious inputs (too long, repetitive)
- Use log rotation (logrotate)
- Forward critical logs to a centralized system (ELK, Splunk, or simple S3 archive)
- Monitor for spikes and alert