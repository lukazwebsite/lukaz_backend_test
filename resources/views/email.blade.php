<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link href="https://fonts.googleapis.com/css?family=Lato:400,700&display=swap" rel="stylesheet" type="text/css">
    <title>Forget Password</title>

</head>
<body>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="text-align: center; padding: 10px 0 30px 0;">
                <img src="{{ asset('assets/logo/coderorbit.png') }}" width="100"/>
            </td>
        </tr>
        <tr>
            <td style="padding-right: 0px;padding-left: 0px; background-color:rgb(56, 138, 85); padding:20px; margin: 20px; text-align:center">
                <img src="{{ asset('assets/image-1.png') }}" width="50"/>
                <h1 style="color:#fff; font-width:blod; padding-top:0; margin-top:0;">Please reset your password</h1>
            </td>
        </tr>

        <tr>
            <td style="padding-right: 0px;padding-left: 0px; padding:20px">
                <p>Hello, {{ $name }} </p>
                <p>We have sent you this email in response to your request to reset your password on Coder Orbit.</p>
                <p>To reset your password, please copy the verification code below:</p>
            </td>
        </tr>

        <tr>
            <td style="padding-right: 0px;padding-left: 0px; background-color:rgb(56, 138, 85); text-align:center">
                <h1 style="color:#fff; font-width:blod; padding:3px; margin:3px;">{{ $pin }}</h1>
            </td>
        </tr>


        <tr>
            <td style="padding-right: 0px;padding-left: 0px; padding:30px">
                <p style="color:#666; font-width:blod; font-size:a8px;">
                    <i>NOTE: Password reset token valid for only 5 minutes</i>
                </p>
                <p>
                    Please ignore this email if you did not request a password change.
                </p>
            </td>
        </tr>


        <tr>
            <td style="overflow-wrap:break-word;word-break:break-word;padding:5px 10px 10px;font-family:'Lato',sans-serif; background-color:rgb(56, 138, 85); ">

                <div style="font-size: 14px; padding:15px; line-height: 140%; text-align: center; word-wrap: break-word;">
                    <img src="{{ asset('assets/image-5.png') }}" width="30"/>
                    <img src="{{ asset('assets/image-4.png') }}" width="30"/>
                    <img src="{{ asset('assets/image-3.png') }}" width="30"/>
                    <img src="{{ asset('assets/image-2.png') }}" width="30"/>
                <p style="line-height: 140%; font-size: 14px;"><span style="font-size: 14px;"><span style="color: #ecf0f1; font-size: 14px;"><span style="line-height: 19.6px; font-size: 14px;">{{ config('app.name') }} &copy;&nbsp; All Rights Reserved</span></span></span></p>
                </div>

            </td>
          </tr>

    </table>

</body>
</html>
