<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <title>Mobile Apps Testing</title>
    <style>
        .table {
            margin-top: 5px;
            text-align: left;
        }

        .has-search .form-control {
            padding-left: 2.375rem;
        }

        .has-search .form-control-feedback {
            position: absolute;
            z-index: 2;
            display: block;
            width: 2.375rem;
            height: 2.375rem;
            line-height: 2.375rem;
            text-align: center;
            pointer-events: none;
            color: #aaa;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Mobile Apps Testing</h1>
        <div class="form-group has-search">
            <span class="fa fa-search form-control-feedback"></span>
            <input type="search" onkeyup="search()" id="search-ticket" class="form-control" placeholder="Search Ticket #">
        </div>
        <button type="button" class="btn btn-outline-primary" onclick="window.open('<?= "manage.php" ?>', target='_blank')">Manage Apps</button>
        <br><br>
        <?php
        require_once "constants.php";
        require_once "utils.php";
        $allSprint = glob(ASSETS . "/*", GLOB_ONLYDIR);
        foreach ($allSprint as $sprint) {
            $sprintFeature = basename($sprint);
            $allTickets = glob(ASSETS . "/" . $sprintFeature . "/*");
            if (!empty($allTickets)) {
                echo '<h2 class="text-body">' . $sprintFeature . '</h2>';
                echo '
            <table id="table-list" class="table table-sm table-borderless">
            <thead>
                <th scope="col">Ticket #</th>
                <th scope="col">Link</th>
            </thead>
            <tbody>';
                foreach ($allTickets as $ticket) {
                    $ticketName = basename($ticket);
                    $firstFileName = substr($ticketName, 0, 1);
                    if (isset($ticketName) && $firstFileName != "_" && $firstFileName != ".") {
                        echo '<tr>';
                        if (isAndroidFileExtension($ticketName)) {
                            echo '<td>';
                            echo '<a href="https://' . $_SERVER['HTTP_HOST'] . "/" . MOBILE_APP_HOST . "/" . ASSETS . "/" . $sprintFeature . "/" . $ticketName . '">' . $ticketName . '</a>';
                            echo '</td>';
                            echo '<td><a class="btn btn-outline-primary btn-sm" href="https://' . $_SERVER['HTTP_HOST'] . "/" . MOBILE_APP_HOST . "/" . ASSETS . "/" . $sprintFeature . "/" . $ticketName . '">' . "Install" . '</a>';
                            echo '</td>';
                        } else {
                            // Assume iOS iPA
                            echo '<td>';
                            echo '<a href=itms-services://?action=download-manifest&url=https://';
                            echo $_SERVER['HTTP_HOST'] . "/" . MOBILE_APP_HOST . "/" . ASSETS . "/" . $sprintFeature . "/" . $ticketName . '/manifest.plist>' . $ticketName;
                            echo '</a>';
                            echo '</td>';
                            echo '<td><a class="btn btn-outline-primary btn-sm" href=itms-services://?action=download-manifest&url=https://' . $_SERVER['HTTP_HOST'] . "/" . MOBILE_APP_HOST . "/" . ASSETS . "/" . $sprintFeature . "/" . $ticketName . '/manifest.plist>' . 'Install</a></td>';
                        }
                        echo '</tr>';
                    }
                }
                echo '
            </tbody>
            </table>
            <br>
            ';
            }
        }
        ?>
    </div>

    <script>
        function search() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("search-ticket");
            filter = input.value.toUpperCase();
            table = document.getElementById("table-list");
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>

</body>

</html>