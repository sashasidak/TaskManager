<!DOCTYPE html>
<html>
<head>
    <title>Jira Current User</title>
</head>
<body>
    <h1>Jira Current User</h1>
    @if(isset($user))
        <p><strong>Display Name:</strong> {{ $user['displayName'] }}</p>
        <p><strong>Email:</strong> {{ $user['emailAddress'] }}</p>
        <p><strong>Time Zone:</strong> {{ $user['timeZone'] }}</p>
        <img src="{{ $user['avatarUrls']['48x48'] }}" alt="Avatar">
    @else
        <p>No user data found.</p>
    @endif
</body>
</html>
