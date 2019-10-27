<html>
<head>
    <title>M PHP Framework</title>
    <link rel="shortcut icon" href="static/favicon.ico" type="image/x-icon">
    <link rel=stylesheet href="css/style.css" type="text/css">
</head>
<body bgcolor=#ffffff>
<!-- NAVBARTOP -->
<table border=0 width="100%" cellpadding=0 cellspacing=0>
    <tr style="background-color: #ffe4b0;">
        <td width="54px" valign=baseline>
            <img src="static/favicon.png" style="width: 50px; height: 50px; margin: 4px;"/>
        </td>
        <td style="padding: 4px;">
            <b><a href="<?php echo $this->homeAddress(); ?>">M PHP Framework Sample App</a></b>
        </td>
        <td style="text-align: right; padding: 4px;">
            <a href="<?php echo $this->homeAddress('/link_one'); ?>">Link One</a>
            <a href="<?php echo $this->homeAddress('/link_two'); ?>">Link Two</a>
            <a href="<?php echo $this->homeAddress('/link_three'); ?>">Link Three</a>
        </td>
    </tr>
</table>
<p/>

<?php $this->renderContent(); ?>

<br />
<p />

<table bgcolor="#ffe4b0" border=0 width="100%" cellpadding=0 cellspacing=0>
    <tr valign=top>
        <td style="padding: 4px;">
            <p>Copyright(c) <?php echo date('Y');?> by Yoppy Yunhasnawa</p>
        </td>
    </tr>
</table>

<br />
</body>
</html>