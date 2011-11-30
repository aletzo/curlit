<?php
require_once 'lib/yaml/sfYamlParser.php';

function getWebPage($url, $method = 'post', $params = false)
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true, // return web page
        CURLOPT_HEADER => false, // don't return headers
        CURLOPT_FOLLOWLOCATION => true, // follow redirects
        CURLOPT_ENCODING => '', // handle all encodings
        CURLOPT_USERAGENT => 'curl_service_bot', // who am i
        CURLOPT_AUTOREFERER => true, // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
        CURLOPT_TIMEOUT => 120, // timeout on response
        CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
    );


    $ch = curl_init();

    curl_setopt_array($ch, $options);

    switch (strtolower($method)) {
        case 'get':
            if (is_array($params)) {
                $url .= '?' . http_build_query($params);
            }
            break;

        case 'post':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            break;

        case 'put':
            if (is_array($params)) {
                $url .= '?' . http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            break;

        case 'delete':
            if (is_array($params)) {
                $url .= '?' . http_build_query($params);
            }

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;

        default:
            break;
    }

    curl_setopt($ch, CURLOPT_URL, $url);

    $content = curl_exec($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);
    $response = curl_getinfo($ch);

    curl_close($ch);

    $response['errno'] = $err;
    $response['errmsg'] = $errmsg;
    $response['content'] = $content;

    return $response;
}

if ($_POST) {
    $yaml = new sfYamlParser();

    $response = getWebPage($_POST['url'], $_POST['method'], $yaml->parse(stripslashes($_POST['params'])));

//    var_dump($yaml->parse($_POST['params']));

    $content = $response['content'];
    $content = json_decode($content, true);
    unset($content['checksum']);

    $checksum = md5(serialize($content) . 'unique_salt_ftw');
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>just curl it</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/bootstrap.min.css" />
        <style type="text/css">
            form .input.offset0,
            div.alert-message.offset0 {
                margin-left: 0;
                margin-bottom: 18px;
            }
            #wrapper {
                margin: 30px auto 0;
            }
            textarea {
                height: 200px;
                width: 500px;
            }
        </style>
    </head>
    <body>
        <div id="wrapper" class="container">
            <div class="row">
                <div class="span12 offset3">
                    <h1>just curl it <small>or just simply test RESTful services!</small></h1>
                    <form action="" method="post">
                        <div class="input offset0">
                            <input class="span8" type="text" name="url" <?php if ($_POST) : ?> value="<?php echo $_POST['url'] ?>" <?php endif ?> placeholder="enter a url" />
                            <input class="btn primary" type="submit" value="go" />
                        </div>
                        <div class="clearfix">
                            <label>method</label>
                            <div class="input">
                                <ul class="inputs-list">
                                    <li>
                                        <label>
                                            <input type="radio" value="delete" name="method" <?php if ($_POST && $_POST['method'] == 'delete')
    echo 'checked="checked"' ?>>
                                            <span>delete</span>
                                        </label>
                                    </li>
                                    <li>
                                        <label>
                                            <input type="radio" value="get" name="method" <?php if ($_POST && $_POST['method'] == 'get')
    echo 'checked="checked"' ?>>
                                            <span>get</span> <span class="label notice">list</span> <span class="label notice">load</span>
                                        </label>
                                    </li>
                                    <li>
                                        <label>
                                            <input type="radio" value="post" name="method" <?php if ($_POST && $_POST['method'] == 'post')
    echo 'checked="checked"' ?>>
                                            <span>post</span> <span class="label notice">create</span>
                                        </label>
                                    </li>
                                    <li>
                                        <label>
                                            <input type="radio" value="put" name="method" <?php if ($_POST && $_POST['method'] == 'put')
    echo 'checked="checked"' ?>>
                                            <span>put</em></span> <span class="label notice">update</span>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="input offset0">
                            <textarea name="params"><?php if ($_POST) {
    echo stripslashes($_POST['params']);
} ?></textarea>
                            <span class="help-inline">enter an array in yaml format</span>
                        </div>
                    </form>

<?php if ($_POST) : ?>
                        <div class="alert-message success span8 offset0">
                            <p>
                                http code: <b><?php echo $response['http_code'] ?></b>
                                <br />
                                content type: <b><?php echo $response['content_type'] ?></b>
                                <br />
                                method: <b><?php echo strtoupper($_POST['method']) ?></b>
                                <br />
                                recalculated checksum: <b><?php echo $checksum ?></b>
                            </p>
                        </div>
                        <div class="input">
                            <textarea><?php echo stripslashes($response['content']) ?></textarea>
                            <span class="help-inline">what the api responded</span>
                        </div>
<?php endif ?>
                </div>
            </div>
        </div>
    </body>
</html>
