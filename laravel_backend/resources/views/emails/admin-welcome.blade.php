<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to FourStep</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">

<tr>
<td style="background:#0f172a;padding:20px;text-align:center;">
    <h1 style="color:#ffffff;margin:0;">
        FourStep Admin Panel
    </h1>
</td>
</tr>

<tr>
<td style="padding:35px;">

<p>Hello <strong>{{ $name }}</strong>,</p>

<p>
Your administrator account has been created successfully.
</p>

<table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse;margin-top:20px;border:1px solid #ddd;">

<tr>
<td width="35%" style="background:#f8f8f8;"><strong>Login URL</strong></td>
<td>
<a href="https://www.fourstepretail.com/admin">
https://www.fourstepretail.com/admin
</a>
</td>
</tr>

<tr>
<td style="background:#f8f8f8;"><strong>Email</strong></td>
<td>{{ $email }}</td>
</tr>

<tr>
<td style="background:#f8f8f8;"><strong>Temporary Password</strong></td>
<td>{{ $password }}</td>
</tr>

</table>

<p style="margin-top:25px;color:#d97706;">
Please change your password after your first login.
</p>

<p style="margin-top:35px;">
Regards,<br>
<strong>FourStep Team</strong>
</p>

</td>
</tr>

<tr>
<td style="background:#f8f8f8;padding:15px;text-align:center;font-size:12px;color:#777;">
© {{ date('Y') }} FourStep. All Rights Reserved.
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>