<?php
function getMailContents($ver_code){
    return sprintf('<body>' .
        '    <h1 style="color: #5e9ca0;">Welcome to our project!</h1>' .
        '    <h2 style="color: #2e6c80;">Verification Code : <strong>' . $ver_code . '</strong></h2>' .
        '    <p>Paste the code into submit box.</p>' .
        '<p>&nbsp;</p>' .
        '<p>Thank you for sign up account.</p>' .
        '<p><strong>&nbsp;</strong></p>' .
        '</body>');
}

