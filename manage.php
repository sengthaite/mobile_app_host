<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>
    <title>Manage Apps</title>
    <style>
        body {
            text-align: center;
        }

        .section {
            margin-top: 25px;
        }

        .folder-name {
            outline: none;
            -webkit-appearance: none;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 10px;
        }

        .sprint-list {
            margin-top: 10px;
        }

        .sprint-list-item {
            margin: 5px;
        }

        .copiable-text {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            margin-bottom: 10px;
            align-items: center;
        }

        .copiable-field {
            margin: 40px;
        }

        .content-title {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            margin-bottom: 10px;
            font-weight: bold;
            align-items: center;
        }

        .app-dir {
            display: flex;
            justify-content: space-between;
            margin: 10px 0 10px 0;
        }

        .hidden {
            display: none;
        }

        .content {
            text-align: left;
            padding: 15px;
        }

        .password {
            padding: 10px;
            position: absolute;
            top: 40%;
            left: 50%;
            margin-left: -20vw;
            width: 40vw;
        }

        .password-background {
            background-color: rgba(100, 100, 100, 0.9);
            position: absolute;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
        }
    </style>
</head>

<body>
    <h1>Manage Apps</h1>
    <div class="container">

        <div class="section">
            <form action="" method="POST">
                <input class="folder-name" type="text" name="SPRINT" id="" placeholder="Enter folder name">
                <input class="btn btn-light" type="submit" value="Add" name="sprint">
            </form>
            <div class="sprint-list">
                <form action="" method="POST">
                    <?php

                    require_once 'constants.php';

                    $dirs = glob(ASSETS . '/*', GLOB_ONLYDIR);
                    foreach ($dirs as $dir) {
                        $dirname = basename($dir);
                        echo '<input class="sprint-list-item btn btn-dark btn-sm" type="submit" name="DIRNAME" value="' . $dirname . '">';
                    }
                    ?>
                </form>
            </div>
        </div>

        <div class="section">
            <form action="" method="POST" enctype="multipart/form-data">
                Select App zip to upload:
                <input class="btn btn-sm" type="file" name="ZIP" id="">
                <input class="btn btn-light btn-sm" type="submit" name="submit" value="submit">
            </form>
        </div>

        <?php

        require_once 'managesprint.php';
        require_once 'manageapp.php';
        require_once 'utils.php';
        require_once 'enum.php';
        require_once 'constants.php';
        if (empty(session_id()) && !headers_sent()) {
            session_start();
        }

        function refresh()
        {
            header('Refresh:0');
        }

        function unsetActionSessions()
        {
            unset($_SESSION['SAVED_ACTIONS']);
            unset($_SESSION['SAVED_DATA']);
        }

        function performActions()
        {
            if (isset($_SESSION['SAVED_DATA']) && isset($_SESSION['SAVED_ACTIONS'])) {
                $sprint_manager = new ManageSprint;
                $manager = new ManageApp;

                $saved_data = $_SESSION['SAVED_DATA'];
                $saved_action = $_SESSION['SAVED_ACTIONS'];

                switch ($saved_action) {
                    case ACTIONS_TYPE::add_sprint:
                        $sprint_manager->addSprint($saved_data);
                        break;
                    case ACTIONS_TYPE::delete_sprint:
                        $sprint_manager->removeSprint($saved_data);
                        unset($_SESSION['selected_dir']);
                        break;
                    case ACTIONS_TYPE::add_app:
                        $manager->addApp($saved_data, $_SESSION['selected_dir']);
                        break;
                    case ACTIONS_TYPE::delete_app:
                        $manager->removeApp($saved_data, $_SESSION['selected_dir']);
                        break;
                    case ACTIONS_TYPE::delete_tmp:
                        if (!removeDirectory(TMP_DIR)) {
                            echo "remove tmp dir failed";
                        }
                        unset($_SESSION['selected_dir']);
                        break;
                    default:
                        break;
                }
                refresh();
                unsetActionSessions();
            }
        }

        function showPasswordForm()
        {
            echo '
                <div class="password-background">
                <div class="password">
                        <form action="" method="post">
                            <input class="folder-name" type="text" name="PASSWORD" id="" placeholder="Enter password" autofocus>
                            <input class="btn btn-primary" type="submit" value="Submit" name="ENTER-PASSWORD">
                            <input class="btn btn-dark" type="submit" value="Cancel" name="CANCEL">
                        </form>
                    </div>
                </div>
                ';
        }

        function saveDataToSession($postData, $actionType)
        {
            $_SESSION['SAVED_DATA'] = $postData;
            $_SESSION['SAVED_ACTIONS'] = $actionType;
        }

        /* Handle server post request */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['SPRINT'])) {
                saveDataToSession($_POST['SPRINT'], ACTIONS_TYPE::add_sprint);
            } else if (isset($_POST["DELETE-DIR"])) {
                saveDataToSession($_POST["DELETE-DIR"], ACTIONS_TYPE::delete_sprint);
            } else if (isset($_FILES['ZIP']) && isset($_SESSION['selected_dir'])) {
                if ($_FILES['ZIP']['type'] !== "application/zip") {
                    echo "Error: Invalid file type (zip only).";
                    return;
                } else if ($_FILES['ZIP']['size'] > 524288000) { // 500MB limit
                    echo "Error: File size exceed 500MB limit";
                    return;
                }

                /* Move tmp_file uploaded to Trash */
                if (!is_dir(TMP_DIR)) {
                    mkdir(TMP_DIR);
                }
                if (!move_uploaded_file($_FILES['ZIP']['tmp_name'], TMP_DIR . "/" . $_FILES['ZIP']['name'])) {
                    echo "Error: Cannot move file to Trash.";
                    return;
                }
                saveDataToSession($_FILES['ZIP'], ACTIONS_TYPE::add_app);
            } else if (isset($_POST["DELETE-APP"]) && isset($_SESSION['selected_dir'])) {
                saveDataToSession($_POST["DELETE-APP"], ACTIONS_TYPE::delete_app);
            } else if (isset($_POST['DIRNAME'])) {
                $_SESSION['selected_dir'] = $_POST['DIRNAME'];
            } else if (isset($_POST["DELETE-TRASH"])) {
                saveDataToSession("", ACTIONS_TYPE::delete_tmp);
            }

            if (isset($_POST['CANCEL'])) {
                refresh();
            } else if (isset($_POST['PASSWORD']) && $_POST['PASSWORD'] === "onlydev") {
                performActions();
            } else if (!isset($_POST['DIRNAME'])) {
                showPasswordForm();
            }
        }

        /* List all the tickets in sprint */
        if (isset($_SESSION['selected_dir'])) {
            $selected_dir = $_SESSION['selected_dir'];
            if ($selected_dir != TMP_DIR) {
                echo '<div class="content-title">iOS Development Archive Links</div>';
                echo '
            <div class="copiable-field">
                <div class="copiable-text">
                    <input type="text" id="app_url" class="flex-fill form-control" placeholder="App URL" value="https://' . $_SERVER['HTTP_HOST'] . "/" . MOBILE_APP_HOST . "/" . ASSETS . "/" . $selected_dir . '/{feature}/app.ipa"/>
                    <button class="copy_btn btn btn-outline-dark" data-clipboard-target="#app_url">copy</button>
                </div>
                <div class="copiable-text">
                    <input type="text" id="display_image" class="flex-fill form-control" placeholder="Display Image URL" value="https://iosz1.z1central.com/img_display.png" />
                    <button class="copy_btn btn btn-outline-dark" data-clipboard-target="#display_image">copy</button>
                </div>
                <div class="copiable-text">
                    <input type="text" id="full_image" class="flex-fill form-control" placeholder="Display Image URL" value="https://iosz1.z1central.com/img_full.png" />
                    <button class="copy_btn btn btn-outline-dark" data-clipboard-target="#full_image">copy</button>
                </div>
            </div>
            ';
            }
            echo '<div class="content-title">';
            echo '<div>' . $selected_dir . '</div>';
            echo '
            <form action="" method="POST">
                <input class="hidden" type="text" name="';
            echo $selected_dir === TMP_DIR ? "DELETE-TRASH" : "DELETE-DIR";
            echo '" value="' . $selected_dir . '">
                <input class="delete-dir btn btn-danger" type="submit" value="Delete">
            </form>
            ';
            echo '</div>';

            echo '<div class="content">';
            $dirs = $selected_dir === TMP_DIR ? glob(TMP_DIR . '/*') : glob(ASSETS . '/' . $selected_dir . '/*');
            foreach ($dirs as $dir) {
                $dirname = basename($dir);
                $firstFileName = substr($dirname, 0, 1);
                if (isset($dirname) && $firstFileName != "_" && $firstFileName != ".") {
                    echo '<div class="app-dir"><div>' . $dirname . '</div>';

                    if ($selected_dir != TMP_DIR) {
                        echo '
                    <form action="" method="POST">
                        <input class="hidden" type="text" name="';
                        echo "DELETE-APP";
                        echo '" value="' . $dirname . '">
                        <input class="delete-dir btn btn-danger btn-sm" type="submit" value="Delete">
                    </form>
                ';
                    }

                    echo '</div>';
                }
            }
            echo '</div>';
        }

        ?>
    </div>
    <script>
        var clipboard = new ClipboardJS('.copy_btn');

        clipboard.on('success', function(e) {
            console.info('Action:', e.action);
            console.info('Text:', e.text);
            console.info('Trigger:', e.trigger);

            e.clearSelection();
        });

        clipboard.on('error', function(e) {
            console.error('Action:', e.action);
            console.error('Trigger:', e.trigger);
        });
    </script>
</body>

</html>