<?php

namespace m;

use PDO;
use PDOException;

class Controller
{
    protected $application;
    protected $view;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->view = new View($this->application);
    }

    protected function redirect($route)
    {
        $location = $this->view->homeAddress($route);

        header("Location: " . $location);
    }

    /*
    public function tampilkanDataMahasiswa($data)
    {
        if($data != null)
        {
            $jumlah = count($data);

            for($i = 0; $i < $jumlah; $i++)
            {
                echo "Mahasiswa Ke-" . $i . ":" . $data[$i];
            }
        }
        else
        {
            echo "Data mahasiswa tidak ditemukan!";
        }
    }

    public function tampilkanDataMahasiswa($data)
    {
        if(count($data) > 0)
        {
            foreach ($data as $key => $mahasiswa)
                echo "Mahasiswa Ke-$key: {$mahasiswa}";

            return;
        }

        echo "Data mahasiswa tidak ditemukan!";
    }

    public function proses()
    {
        $j = json_encode(file_get_contents("C:\\sql\data.txt") . file_get_contents("D:\\label\data.tx"));
        if($j)
            echo $j;
        else
            echo json_encode(array('status' => '400', 'm' => 'gagal!'));
    }

    public function tampilkanJsonGabunganDataLabel()
    {
        $fileData  = file_get_contents("C:\\sql\data.txt");
        $fileLabel = file_get_contents("D:\\label\data.tx");

        $gabung = $fileData . $fileLabel;

        if(!empty($gabung))
            $json = json_encode($gabung);
        else
        {
            $payloadError = array(
                'status'  => 400,
                'message' => 'Gagal menampilkan data label.'
            );

            $json = json_encode($payloadError);
        }

        echo $json;
    }

    function hitung()
    {
        $a = $_POST['a'];
        $aa = $_POST['aa'];

        $bbb = $a * $a;
        $ccc = $aa * $aa;

        $ok = sqrt($bbb + $ccc);

        echo $ok;
    }

    function hitungSisiMiring()
    {
        $sisiTegak = $_POST['sisi_tegak'];
        $sisiLurus = $_POST['sisi_lurus'];

        $t2 = $sisiTegak * $sisiTegak;
        $l2 = $sisiLurus * $sisiLurus;

        $hasil = sqrt($t2 + $l2);

        echo $hasil;
    }

    function simpanMahasiswa()
    {
        if(isset($_POST['submit']))
        {
            $nama = $_POST['nama'];
            $alamat = $_POST['alamat'];

            try
            {
                $db = new PDO
                (
                    "mysql:host=localhost;dbname=akademik",
                    'root',
                    ''
                );
            }
            catch(PDOException $e)
            {
                die("Error! Details: " . $e->getMessage());
            }

            $db->exec("INSERT INTO mahasiswa VALUES ('$nama', '$alamat')");
        }
    }

    public function simpanMahasiswa()
    {
        if(isset($_POST['submit']))
        {
            $this->insertMahasiswa(
                $_POST['nama'],
                $_POST['alamat'];
            );
        }
    }

    private function insertMahasiswa($nama, $alamat)
    {
        $sql = "INSERT INTO mahasiswa VALUES ('$nama', '$alamat')";

        $this->connectDb()->exec($sql);
    }

    private function connectDb()
    {
        try
        {
            return new PDO
            (
                "mysql:host=localhost;dbname=akademik",
                'root',
                ''
            );
        }
        catch(PDOException $e)
        {
            die("Error! Details: " . $e->getMessage());
        }
    }

    private function imporDaftarMahasiswa($daftarMahasiswa)
    {
        foreach ($dataMahasiswa as $mhs)
        {
            $nama   = $mhs['nama'];
            $alamat = $mhs['alamat'];

            $sql = "INSERT INTO mahasiswa VALUES ('$nama', '$alamat')";

            $this->connectDb()->exec($sql);
        }
    }

    private function imporDaftarMahasiswa($daftarMahasiswa)
    {
        $sql = 'INSERT INTO mahasiswa VALUES ';

        foreach ($daftarMahasiswa as $mhs)
        {
            $nama   = $mhs['nama'];
            $alamat = $mhs['alamat'];

            $sql .= "('$nama', '$alamat'),";
        }

        rtrim($sql, ', '); // Hilangkan koma (,) di akhir

        $this->connectDb()->exec($sql);
    }
    */
}