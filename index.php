<?php

$host = 'localhost';
$user='root';
$pass='';
$db='test_laravel';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
  echo  $mysqli->connect_error;
  exit;
}

$mysqli->set_charset("utf8");
$submitted = 0;

if(isset($_POST['submit'])){
  $submitted = 1;
  $searchDate = $_POST['date'];
  $res = $mysqli->query("SELECT `data` FROM `phpdebugbar` WHERE date(`meta_datetime`) = '".$mysqli->real_escape_string($searchDate)."'");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profiler</title>
  <link rel="stylesheet" href="//cdn.jsdelivr.net/jquery.ui/1.11.4/themes/blitzer/jquery-ui.min.css">
  <style>
  #query-result{
    margin-top: 20px;
  }
  </style>
</head>
<body>

  <div id="query-form">
    <legend>Search Data</legend>
    <fieldset>
      <form action="" method="post">
        <input type="text" name="date" class="date" value="<?php echo isset($_POST['date']) ? $_POST['date'] : ''?>">
        <input type="submit" name="submit" value="Search">
      </form>
      
    </fieldset>
  </div>
  
  <!-- show all search result -->
  <div id="query-result">
    <?php if($submitted == 1){?>
    <legend>Search Result</legend>
    <fieldset>
      <?php
      //if (mysqli_num_rows($res) == 0) {
      if ($res->num_rows == 0) {
        echo 'No record found';
      }else{
        ?>
        <div id="accordion">
          <?php
            $slug=0;
            while ($row = $res->fetch_object()) {
              $data = unserialize($row->data);
              /*echo '<pre>';
              print_r($data);
              exit;*/
          ?>
        <h3><?php echo 'Time: '.$data['__meta']['datetime']?></h3>
        <div>    
          <div class="tabs">
            <ul>
              <li><a href="#meta-<?=$slug?>">Meta</a></li>
              <li><a href="#message-<?=$slug?>">Messages</a></li>
              <li><a href="#timeline-<?=$slug?>">Timeline</a></li>
              <li><a href="#memory-<?=$slug?>">Memory</a></li>
              <li><a href="#exception-<?=$slug?>">Exceptions</a></li>
              <li><a href="#view-<?=$slug?>">Views</a></li>
              <li><a href="#route-<?=$slug?>">route</a></li>
              <li><a href="#query-<?=$slug?>">queries</a></li>
              <li><a href="#mail-<?=$slug?>">mails</a></li>
              <li><a href="#session-<?=$slug?>">session</a></li>
              <li><a href="#request-<?=$slug?>">request</a></li>
            </ul>
            <div id="meta-<?=$slug?>">
              <p>Date time: <?=$data['__meta']['datetime']?></p>
              <p>Method: <?=$data['__meta']['method']?></p>
              <p>URI: <?=$data['__meta']['uri']?></p>
              <p>IP Address: <?=$data['__meta']['ip']?></p>
            </div>
            
            <div id="message-<?=$slug?>">
              <?php
              if($data['messages']['messages']){
                foreach ($data['messages']['messages'] as $message) {
                  echo '<p>'.$message.'</p>';
                }
                unset($message);
              }else{
                echo '<p>No message found</p>';
              }
              ?>
            </div>

            <div id="timeline-<?=$slug?>">
              <p>Start: <?=date('Y-m-d H:i:s',$data['time']['start'])?></p>
              <p>End: <?=date('Y-m-d H:i:s',$data['time']['end'])?></p>
              <p>Duration: <?=$data['time']['duration_str']?></p>
              <?php
              if($data['time']['measures']){
                foreach ($data['time']['measures'] as $measure) {
                  echo '<br/>'.$measure['label'].':<br/> Start: '.date('Y-m-d H:i:s',$measure['start']).'  End: '.date('Y-m-d H:i:s',$measure['end']).'  Duration: '.$measure['duration_str'];
                }
                unset($measure);
              }
              ?>
            </div>
            
            <div id="memory-<?=$slug?>">
              <?php echo '<p>'.$data['memory']['peak_usage_str'].'</p>';?>
            </div>
            
            <div id="exception-<?=$slug?>">
              <?php
              if($data['exceptions']['exceptions']){
                foreach ($data['exceptions']['exceptions'] as $exception) {
                  echo '<p>'.$exception.'</p>';
                }
                unset($exception);
              }
              ?>
            </div>

            <div id="view-<?=$slug?>">
              <?php
              if($data['views']['templates']){
                foreach ($data['views']['templates'] as $view) {
                  echo '<p>'.$view['name'].'</p>';
                }
                unset($view);
              }
              ?>
            </div>

            <div id="route-<?=$slug?>">
              <p>URI: <?php echo $data['route']['uri']?></p>
              <p>Middleware: <?php echo $data['route']['middleware']?></p>
              <p>Controller: <?php echo $data['route']['controller']?></p>
              <p>Namespace: <?php echo $data['route']['namespace']?></p>
              <p>Prefix: <?php echo !empty($data['route']['prefix'])? $data['route']['prefix'] : 'null'?></p>
              <p>File: <?php echo $data['route']['file']?></p>
            </div>
            <div id="query-<?=$slug?>">
              <?php
              if($data['queries']){
                echo '<p>Total Duration: '.$data['queries']['accumulated_duration_str'].'</p>';
                if($data['queries']['statements']){
                  $i=1;
                  foreach ($data['queries']['statements'] as $query) {
                    echo '<pre><strong>Query '.$i.'.</strong><br/> ';
                    echo 'Duration: '.$query['duration_str'].'</p>';
                    echo '<p>Query: '.$query['sql'].'</p>';
                    echo '<p>params: ';
                    if(!empty($query['params'])){
                      foreach ($query['params'] as $key => $param) {
                        if($key!='hints'){
                          echo $param.', ';
                        }
                      }
                      unset($param);
                    }
                    echo '</p>';
                    echo '</pre>';
                    $i++;
                  }
                  unset($query);
                  unset($i);
                }
              }
              ?>
            </div>
            <div id="mail-<?=$slug?>">
              <?php
              if($data['swiftmailer_mails']){
                echo '<p>Total mails: '.$data['swiftmailer_mails']['count'].'</p>';
                if(!empty($data['swiftmailer_mails']['count'])){
                  foreach ($data['swiftmailer_mails']['count'] as $mail) {
                    echo $mail.'<br/>';
                  }
                  unset($mail);
                }
              }
              ?>
            </div>
            <div id="session-<?=$slug?>">
              <?php
              if($data['session']){
                echo '<p>_token: '.$data['session']['_token'].'</p>';
                echo '<p>PHPDEBUGBAR_STACK_DATA:</p>';
                echo '<pre>';
                print_r($data['session']['PHPDEBUGBAR_STACK_DATA']);
                echo '</pre>';
              }
              ?>
            </div>
            <div id="request-<?=$slug?>">
              <?php
              if($data['request']){
                echo '<p>format : '.$data['request']['format'].'</p>';
                echo '<p>content_type : '.$data['request']['content_type'].'</p>';
                echo '<p>status_text : '.$data['request']['status_text'].'</p>';
                echo '<p>status_code : '.$data['request']['status_code'].'</p>';
                echo '<p>request_query : <br/><pre>'; print_r($data['request']['request_query']); echo '</pre></p>';
                echo '<p>request_request : <br/><pre>'; print_r($data['request']['request_request']); echo '</pre></p>';
                echo '<p>request_headers : <br/><pre>'; print_r($data['request']['request_headers']); echo '</pre></p>';
                echo '<p>request_server : <br/><pre>'; print_r($data['request']['request_server']); echo '</pre></p>';
                echo '<p>request_cookies : <br/><pre>'; print_r($data['request']['request_cookies']); echo '</pre></p>';
                echo '<p>response_headers : <br/><pre>'; print_r($data['request']['response_headers']); echo '</pre></p>';
                echo '<p>path_info : '.$data['request']['path_info'].'</p>';
                echo '<p>session_attributes : <br/><pre>'; print_r($data['request']['session_attributes']); echo '</pre></p>';
              }
              ?>
            </div>
          </div>
        </div>
        <?php
        $slug++;
      }

  }?>
</div>
<?php } ?>
</fieldset>

</div>

<script src="//cdn.jsdelivr.net/jquery/2.1.4/jquery.min.js"></script>
<script src="//cdn.jsdelivr.net/jquery.ui/1.11.4/jquery-ui.min.js"></script>
<script>
$(function() {
  $( ".tabs" ).tabs();
  
  $( ".date" ).datepicker({
    dateFormat: "yy-mm-dd"
  });
  
  //$( ".date"  ).datepicker( "setDate", new Date() );
  
  $( "#accordion" ).accordion({
    collapsible: true,
    active: false
  });
});
</script>

</body>
</html>

