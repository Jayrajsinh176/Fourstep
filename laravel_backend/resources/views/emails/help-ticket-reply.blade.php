<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reply to your Help Ticket</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">

<tr>
<td style="background:#B0422E;padding:20px;text-align:center;">
    <h1 style="color:#ffffff;margin:0;font-size:22px;">
        Fourstep Retail
    </h1>
</td>
</tr>

<tr>
<td style="padding:35px;">

<p style="margin:0 0 10px;">Hello <strong>{{ $ticket->member->fullname ?? 'Customer' }}</strong>,</p>

<p style="margin:0 0 25px;color:#444;">
Our support team has replied to your help ticket. Here are the details:
</p>

<table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse;border:1px solid #ddd;">

<tr>
<td width="30%" style="background:#f8f8f8;"><strong>Ticket #</strong></td>
<td>{{ $ticket->id }}</td>
</tr>

<tr>
<td style="background:#f8f8f8;"><strong>Category</strong></td>
<td>{{ $ticket->category }}</td>
</tr>

<tr>
<td style="background:#f8f8f8;"><strong>Subject</strong></td>
<td>{{ $ticket->subject }}</td>
</tr>

<tr>
<td style="background:#f8f8f8;"><strong>Your Message</strong></td>
<td>{{ $ticket->details }}</td>
</tr>

</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;">
<tr>
<td style="background:#fdf0ed;border:1px solid #e8866e;border-radius:8px;padding:18px;">
    <div style="font-weight:bold;color:#B0422E;margin-bottom:8px;">Support Reply</div>
    <div style="color:#333;white-space:pre-line;">   {{ $ticket->admin_reply ?? 'No reply was provided.' }}</div>
</td>
</tr>
</table>

<p style="margin:28px 0 0;color:#777;font-size:13px;">
If you have further questions, please reply through the Help Center in your account.
</p>

<p style="margin-top:35px;">
Regards,<br>
<strong>Fourstep Team</strong>
</p>

</td>
</tr>

<tr>
<td style="background:#f8f8f8;padding:15px;text-align:center;font-size:12px;color:#777;">
&copy; {{ date('Y') }} Fourstep Retail. All Rights Reserved.
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>
