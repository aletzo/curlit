<?php
require_once 'lib/yaml/sfYamlParser.php';

function getWebPage($url, $method = 'post', $params = [], $headers = [])
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
            if ($params) {
                $url .= '?' . http_build_query($params);
            }
            break;

        case 'post':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            break;

        case 'put':
            if ($params) {
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

    if ($headers) {
        $headers = explode("\n", $headers);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $content  = curl_exec($ch);
    $err      = curl_errno($ch);
    $errmsg   = curl_error($ch);
    $response = curl_getinfo($ch);

    curl_close($ch);

    $response['errno']   = $err;
    $response['errmsg']  = $errmsg;
    $response['content'] = $content;

    return $response;
}

if ($_POST) {
    $yaml = new sfYamlParser();

    $response = getWebPage(
        $_POST['url'],
        $_POST['method'],
        $yaml->parse(stripslashes($_POST['body'])),
        stripslashes($_POST['headers'])
    );

//    var_dump($yaml->parse($_POST['body']));

    $content = $response['content'];
    $content = json_decode($content, true);

    unset($content['checksum']);

    $checksum = md5(serialize($content) . 'unique_salt_ftw');
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>just cURL it</title>
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
                height: 100px;
                width: 500px;
            }
            textarea#body {
                height: 200px;
            }
        </style>
    </head>
    <body>
        <div id="wrapper" class="container">
            <div class="row">
                <div class="span12 offset3">
                    <h1>just cURL it <small>or just simply test RESTful services!</small></h1>
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
                                            <input type="radio" value="delete" name="method" <?php if ($_POST && $_POST['method'] == 'delete') { echo 'checked="checked"'; } ?>>
                                            <span>delete</span>
                                        </label>
                                    </li>
                                    <li>
                                        <label>
                                            <input type="radio" value="get" name="method" <?php if ($_POST && $_POST['method'] == 'get' || ! $_POST) { echo 'checked="checked"'; } ?>>
                                            <span>get</span> <span class="label notice">list</span> <span class="label notice">load</span>
                                        </label>
                                    </li>
                                    <li>
                                        <label>
                                            <input type="radio" value="post" name="method" <?php if ($_POST && $_POST['method'] == 'post') { echo 'checked="checked"'; } ?>>
                                            <span>post</span> <span class="label notice">create</span>
                                        </label>
                                    </li>
                                    <li>
                                        <label>
                                            <input type="radio" value="put" name="method" <?php if ($_POST && $_POST['method'] == 'put') { echo 'checked="checked"'; } ?>>
                                            <span>put</em></span> <span class="label notice">update</span>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="input offset0">
                            <textarea name="headers" id="headers"><?php if ($_POST) {
    echo stripslashes($_POST['headers']);
} ?></textarea>
                            <span class="help-inline">request headers in <a href="https://en.wikipedia.org/wiki/YAML" target="_blank">YAML</a> format</span>
                        </div>
                        <div class="input offset0">
                            <textarea name="body" id="body"><?php if ($_POST) {
    echo stripslashes($_POST['body']);
} ?></textarea>
                            <span class="help-inline">request body in <a href="https://en.wikipedia.org/wiki/YAML" target="_blank">YAML</a> format</span>
                        </div>
                    </form>

<?php if ($_POST) : ?>
                        <div class="alert-message <?php echo $response['errno'] ? 'error' : 'success' ?> span8 offset0">
                            <p>
                                HTTP Code: <b><?php echo $response['http_code'] ?></b>
                                <br>
                                Content Type: <b><?php echo $response['content_type'] ?></b>
                                <br>
                                Method: <b><?php echo strtoupper($_POST['method']) ?></b>
                                <br>
                                Recalculated Checksum: <b><?php echo $checksum ?></b>

                                <?php if ($response['errno']) : ?>
                                <br>
                                Error: <b><?php echo $response['errno'] . ' - ' .$response['errmsg'] ?></b>
                                <?php endif ?>
                            </p>
                        </div>
                        <div class="input">
                            <textarea><?php echo stripslashes($response['content']) ?></textarea>
                            <span class="help-inline">what the API responded</span>
                        </div>
                        <?php if ($content) : ?>
                        <br>
                        <br>
                        <pre class="span8 offset0">
                        <?php print_r($content) ?>
                        </pre>
                        <?php endif ?>
<?php endif ?>
                </div>
            </div>
        </div>
    </body>
</html>
