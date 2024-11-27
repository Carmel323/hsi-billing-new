<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
</head>

<body>

    <div style="font-family: Verdana, sans-serif; background-color: #EEF3FB; margin: 0; padding: 0;padding:30px 0px;">
        <div style="max-width: 600px; margin: 0 auto;">

            <table cellspacing="0" cellpadding="0" border="0" width="100%">
                <!-- Header -->
                <tr>
                    <td>
                        <div style="margin: 0 auto;">
                            <table cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: white;display:none;">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="https://www.clearlink.com/" target="_blank" rel="noopener noreferrer" style="text-decoration: none;">
                                            <img src="https://partner-program.stage.clear.link/assets/images/cl_logo.svg" alt="Clearlink logo" width="150" height="37" style="border: 0;">
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
                <!-- Content -->
                <tr>
                    <td style="background-color: white;padding:30px 0px;">

                        <table style="text-align:center;margin:10px auto;width: 100%;">
                            <tbody>
                                <tr>
                                    <td>
                                        @yield('content')
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table style="text-align:left;margin:10px auto;width:100%;">
                            <tbody>
                                <tr>
                                    <td style="padding:0px 50px 50px 50px;">
                                        <p style="margin-bottom:5px;font-size:15px;color:black !important;">Yours,</p>
                                        <p style="margin-top:5px;font-size:15px;color:black !important;">Clearlink Technologies LLC</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <center>
                                            <div style="text-align:center;margin:0 auto;max-width:100px;display:inline-block;width:100px;height:auto;margin:0 auto;color:#000;background:#fff;text-decoration:none">
                                                <img src="https://partner-program.stage.clear.link/assets/images/clearlink-email-logo.png" style="max-width:100%;width:120px;height:auto;max-height:50px">
                                            </div>
                                        </center>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <!-- Footer -->
                <tr>
                    <td style="background-color: #EEF3FB;">
                        <div style="max-width: 600px; margin: 0 auto; padding: 10px 0;text-align:center;">
                            <table cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #EEF3FB;">
                                <tr>
                                    <td align="center" style="padding: 10px 0 10px 0;">
                                        <table cellspacing="0" cellpadding="0" border="0" width="600">

                                            <tr>
                                                <td style="font-size: 12px; color: rgb(149, 149, 149); font-family: Verdana, sans-serif; font-weight: 400; text-align: center; line-height: 18px;">Clearlink Technologies LLC<br>42 Future Way, Draper, UT 84020
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 12px; color: rgb(149, 149, 149); font-family: Verdana, sans-serif; font-weight: 400; text-align: center; line-height: 18px;"><a href="https://www.clearlink.com/privacy" style="color:#444;font-size:13px;margin: 20px auto;    display: block;" target="_blank"><span style="color:#444;font-size:13px">Privacy policy and Terms</span></a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>

</html>