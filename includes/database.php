<?php
$connect = mysqli_connect('localhost', 'cms', 'password', 'cms', 3307);
// localhost er vores lokale server,
//  cms er vores database brugernavn, 
//  secret er vores database password 
//  og cms er vores database navn
//+ port navn

if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
} ?>

