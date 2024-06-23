import os

def search_phpmailer_mentions(directory):
    phpmailer_mentions = []
    for root, dirs, files in os.walk(directory):
        for file in files:
            if file.endswith('.php'):
                file_path = os.path.join(root, file)
                with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                    lines = f.readlines()
                    for i, line in enumerate(lines):
                        if 'PHPMailer' in line or 'require' in line or 'include' in line:
                            phpmailer_mentions.append((file_path, i + 1, line.strip()))
    return phpmailer_mentions

mentions = search_phpmailer_mentions('./php/')
mentions[:20]  # Display the first 20 mentions to get an idea
