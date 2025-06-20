<?php
function koneksiToko(){
    $servername="localhost";
    $username="root";
    $password="";
    $database="warung";
    $koneksi = mysqli_connect($servername, $username, $password, $database);
    if (!$koneksi){
        echo "gagal koneksi njir";
    }
    return $koneksi;
}
?>