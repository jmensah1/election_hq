<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Invitation</title>
</head>
<body style="font-family: sans-serif; background-color: #f8fafc; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 24px;">
            <h1 style="color: #0f172a; font-size: 24px; font-weight: bold;">Invitation to Nominate</h1>
        </div>
        
        <p style="color: #334155; font-size: 16px; line-height: 1.5;">
            Hello,
        </p>
        
        <p style="color: #334155; font-size: 16px; line-height: 1.5;">
            You have been nominated for the position of <strong>{{ $candidate->position->name }}</strong> in the upcoming election: <strong>{{ $candidate->election->title }}</strong>.
        </p>

        <p style="color: #334155; font-size: 16px; line-height: 1.5;">
            Please visit the Candidate Portal to accept your nomination and complete your profile.
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('candidate.portal') }}" style="display: inline-block; background-color: #f59e0b; color: #ffffff; font-weight: bold; padding: 12px 24px; border-radius: 6px; text-decoration: none;">
                Go to Candidate Portal
            </a>
        </div>

        <p style="color: #334155; font-size: 16px; line-height: 1.5; background-color: #eff6ff; padding: 16px; border-radius: 6px; border-left: 4px solid #3b82f6;">
            <strong>Important:</strong> Please log in using your registered Google email address <strong>({{ $candidate->email }})</strong> to access your dashboard.
        </p>

        <p style="color: #64748b; font-size: 14px; margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
            If you did not expect this invitation, please contact the electoral Commissioner.
        </p>
    </div>
</body>
</html>
