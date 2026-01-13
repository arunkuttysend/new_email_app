import csv
import os

source_file = '/Users/arun/new_emailpl/smartlead_bulk_upload_1050_accounts.csv'
dest_file = 'import_ready_smartlead_inboxes.csv'

print(f"Converting {source_file} to {dest_file}...")

with open(source_file, 'r', newline='') as infile, open(dest_file, 'w', newline='') as outfile:
    reader = csv.DictReader(infile)
    # Define destination fieldnames
    fieldnames = [
        'name', 'from_name', 'from_email', 
        'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption', 
        'imap_host', 'imap_port', 'imap_username', 'imap_password', 'imap_encryption'
    ]
    writer = csv.DictWriter(outfile, fieldnames=fieldnames)
    writer.writeheader()
    
    count = 0
    for row in reader:
        # Smartlead headers:
        # from_name,from_email,user_name,password,smtp_host,smtp_port,
        # imap_host,imap_port,max_email_per_day,custom_tracking_url,warmup_enabled,
        # total_warmup_per_day,daily_rampup,reply_rate_percentage,bcc,signature,
        # different_reply_to_address,imap_user_name,imap_password

        from_name = row.get('from_name', '')
        from_email = row.get('from_email', '')
        
        # Determine encryption based on ports
        smtp_port = row.get('smtp_port', '587')
        smtp_enc = 'ssl' if smtp_port == '465' else 'tls'
        
        imap_port = row.get('imap_port', '993')
        imap_enc = 'ssl' if imap_port == '993' else 'tls'

        writer.writerow({
            'name': from_name if from_name else from_email,
            'from_name': from_name,
            'from_email': from_email,
            'smtp_host': row.get('smtp_host', ''),
            'smtp_port': smtp_port,
            'smtp_username': row.get('user_name', ''), # Mapping user_name to smtp_username
            'smtp_password': row.get('password', ''),
            'smtp_encryption': smtp_enc,
            'imap_host': row.get('imap_host', ''),
            'imap_port': imap_port,
            'imap_username': row.get('imap_user_name', ''),
            'imap_password': row.get('imap_password', ''),
            'imap_encryption': imap_enc
        })
        count += 1

print(f"✅ Converted {count} rows successfully.")
