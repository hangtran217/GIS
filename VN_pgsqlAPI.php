<?php

    if (isset($_POST['functionname'])) 
    {
        $paPDO = initDB();
        $paSRID = '4326';
        $paPoint = $_POST['paPoint'];

        $functionname = $_POST['functionname'];

        $aResult = "null";

        if ($functionname == 'getGeoVNToAjax')
            $aResult = getGeoVNToAjax($paPDO, $paSRID, $paPoint);
        else if ($functionname == 'getInfoVNToAjax')
            $aResult = getInfoVNToAjax($paPDO, $paSRID, $paPoint);
        else if ($functionname == 'getInfoRiveroAjax')
            $aResult = getInfoRiverToAjax($paPDO, $paSRID, $paPoint);
        else if ($functionname == 'getInfoHyproPowerToAjax')
            $aResult = getInfoHyproPowerToAjax($paPDO, $paSRID, $paPoint);
        else if ($functionname == 'getGeoHyproPowerToAjax')
            $aResult = getGeoHyproPowerToAjax($paPDO, $paSRID, $paPoint);
        else if ($functionname == 'getGeoRiverToAjax')
            $aResult = getGeoRiverToAjax($paPDO, $paSRID, $paPoint);

        echo $aResult;

        closeDB($paPDO);
    }
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
        $aResult = seacherCity($paPDO, $paSRID, $name);
        echo $aResult;
    }

    function initDB()
    {
        // Kết nối CSDL
        $paPDO = new PDO('pgsql:host=localhost;dbname=dams_VietNam;port=5432', 'postgres', '2107');
        return $paPDO;
    }
    function query($paPDO, $paSQLStr)
    {
        try {
            // Khai báo exception
            $paPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Sử đụng Prepare 
            $stmt = $paPDO->prepare($paSQLStr);
            // Thực thi câu truy vấn
            $stmt->execute();

            // Khai báo fetch kiểu mảng kết hợp
            $stmt->setFetchMode(PDO::FETCH_ASSOC);

            // Lấy danh sách kết quả
            $paResult = $stmt->fetchAll();
            return $paResult;
        } catch (PDOException $e) {
            echo "Thất bại, Lỗi: " . $e->getMessage();
            return null;
        }
    }
    function closeDB($paPDO)
    {
        // Ngắt kết nối
        $paPDO = null;
    }

    // hightlight VN
    function getGeoVNToAjax($paPDO, $paSRID, $paPoint)
    {
        
        $paPoint = str_replace(',', ' ', $paPoint);
        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"gadm36_vnm_1\" where ST_Within('SRID=4326;" . $paPoint . "'::geometry,geom)";
        $result = query($paPDO, $mySQLStr);
        if ($result != null) {
            // Lặp kết quả
            foreach ($result as $item) {
                return $item['geo'];
            }
        } else
            return "null";
    }
    // hightlight Thuy dien
    function getGeoHyproPowerToAjax($paPDO, $paSRID, $paPoint)
    {
        
        $paPoint = str_replace(',', ' ', $paPoint);
        
        $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
        $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom))) from hydropower_dams";
        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from hydropower_dams where " . $strDistance . " = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";
        $result = query($paPDO, $mySQLStr);

        if ($result != null) {
            // Lặp kết quả
            foreach ($result as $item) {
                return $item['geo'];
            }
        } else
            return "null";
    }

    // hightlight Song
    function getGeoRiverToAjax($paPDO, $paSRID, $paPoint)
    {
       
        $paPoint = str_replace(',', ' ', $paPoint);
        
        $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
        $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom))) from river";
        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from river where " . $strDistance . " = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";
        $result = query($paPDO, $mySQLStr);

        if ($result != null) {
            // Lặp kết quả
            foreach ($result as $item) {
                return $item['geo'];
            }
        } else
            return "null";
    }

    // Truy van thong tin VN
    function getInfoVNToAjax($paPDO, $paSRID, $paPoint)
    {
       
        $paPoint = str_replace(',', ' ', $paPoint);
        $mySQLStr = "SELECT gid, name_1, ST_Area(geom) as dt, ST_Perimeter(geom) as cv from \"gadm36_vnm_1\" where ST_Within('SRID=4326;" . $paPoint . "'::geometry,geom)";
        
        $result = query($paPDO, $mySQLStr);

        if ($result != null) {
            $resFin = '<table>';
            // Lặp kết quả
            foreach ($result as $item) {
                $resFin = $resFin . '<tr><td>Mã Vùng: ' . $item['gid'] . '</td></tr>';
                $resFin = $resFin . '<tr><td>Tên Tỉnh: ' . $item['name_1'] . '</td></tr>';
                $resFin = $resFin . '<tr><td>Diện Tích: ' . $item['dt'] . ' km2 ' .'</td></tr>';
                $resFin = $resFin . '<tr><td>Chu vi: ' . $item['cv'] . ' km '.'</td></tr>';
                break;
            }
            $resFin = $resFin . '</table>';
            return $resFin;
        } else
            return "null";
    }

    //Truy van thong tin Song 
    function getInfoRiverToAjax($paPDO, $paSRID, $paPoint)
    {
        $paPoint = str_replace(',', ' ', $paPoint);
        $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
        $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom))) from river";
        $mySQLStr = "SELECT *  from river where " . $strDistance . " = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";
        $result = query($paPDO, $mySQLStr);

        if ($result != null) {
            $resFin = '<table>';
            // Lặp kết quả
            foreach ($result as $item) {
                $resFin = $resFin . '<tr><td>Tên Sông: ' . $item['ten'] . '</td></tr>';
                $resFin = $resFin . '<tr><td>Chiều dài: ' . $item['chieu_dai'] . '</td></tr>';
                break;
            }
            $resFin = $resFin . '</table>';
            return $resFin;
        } else
            return "null";
    }

    // truy van thong tin thuy dien
    function getInfoHyproPowerToAjax($paPDO, $paSRID, $paPoint)
    {
        $paPoint = str_replace(',', ' ', $paPoint);
        $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
        $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom))) from hydropower_dams";
        $mySQLStr = "SELECT * from hydropower_dams where " . $strDistance . " = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";

        $result = query($paPDO, $mySQLStr);

        if ($result != null) {
            $resFin = '<table>';
            // Lặp kết quả
            foreach ($result as $item) {
                $resFin = $resFin . '<tr><td>Tên: ' . $item['name'] . '</td></tr>';
                $resFin = $resFin . '<tr><td>Quy mô: ' . $item['quy_mo'] . '</td></tr>';
                $resFin = $resFin . '<tr><td>Kinh độ: ' . $item['long'] . '</td></tr>';
                $resFin = $resFin . '<tr><td>Vĩ độ: ' . $item['lat'] . '</td></tr>';
                break;
            }
            $resFin = $resFin . '</table>';
            return $resFin;
        } else
            return "null";
    }

    //tim kiem
    function seacherCity($paPDO, $paSRID, $name)
    {
        
        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from gadm36_vnm_1 where name_1 like '$name'";
        $result = query($paPDO, $mySQLStr);

        if ($result != null) {
            // Lặp kết quả
            foreach ($result as $item) {
                return $item['geo'];
            }
        } else
            return "null";
    }
?>