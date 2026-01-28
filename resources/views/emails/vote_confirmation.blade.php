<!DOCTYPE html>
<html>
<head>
    <title>Vote Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">
        <h2 style="color: #2563eb;">Vote Confirmation</h2>
        <p>Dear {{ $name }},</p>
        
        <p>This email confirms that your vote has been successfully cast in the election: <strong>{{ $electionTitle }}</strong>.</p>
        
        <p><strong>Time of Vote:</strong> {{ $time }}</p>
        
        <p>For security and anonymity reasons, this confirmation does not list your specific choices. However, your participation has been recorded in our secure, anonymous ledger.</p>
        
        <p>If you did not authorized this action, please contact the election commission immediately.</p>
        
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        
        <p style="font-size: 12px; color: #666;">
            This is an automated message from Elections HQ. Please do not reply to this email.
        </p>
    </div>
</body>
</html>
