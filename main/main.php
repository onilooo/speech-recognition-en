<?php

$secret_key = "c7e8c1c4effb4b5c97ed7caeda19758f";

# loads the audio
$filesize = filesize('/demo_record/demo.mp3');
$fp = fopen('/demo_record/demo.mp3', 'rb');
// read the entire file into a binary string
$binary = fread($fp, $filesize);

# endpoint and options to start a transcription task
$endpoint = "https://api.speechtext.ai/recognize?key=".$secret_key."&language=en-US&punctuation=true&format=m4a";
$header = array('Content-type: application/octet-stream');

# curl connection initialization
$ch = curl_init();

# curl options
curl_setopt_array($ch, array(
    CURLOPT_URL => $endpoint,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HEADER => false,
    CURLOPT_HTTPHEADER => $header,
    CURLOPT_POSTFIELDS => $binary,
    CURLOPT_FOLLOWLOCATION => true
));

# send an audio transcription request
$body = curl_exec($ch);

if (curl_errno($ch))
{
    echo "CURL error: ".curl_error($ch);
}
else
{
    # parse JSON results
    $r = json_decode($body, true);
    # get the id of the speech recognition task
    $task = $r['id'];
    echo "Task ID: ".$task."\r\n";
    
    # endpoint to check status of the transcription task and retrieve results
    $endpoint = "https://api.speechtext.ai/results?key=".$secret_key."&task=".$task."&summary=true&summary_size=15&highlights=true&max_keywords=15";
    curl_setopt_array($ch, array(
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => false,
        CURLOPT_HEADER => false,
        CURLOPT_FOLLOWLOCATION => true
    ));
    echo "Get transcription results, summary, and highlights\r\n";
    # use a loop to check if the task is finished
    while (true)
    {
        $body = curl_exec($ch);
        $results = json_decode($body, true);
        echo "Task status: ".$results['status']."\r\n";
        if (!array_key_exists('status', $results))
        {
            break;
        }
        if ($results['status'] == 'failed')
        {
            echo "The task is failed!\r\n";
        }
        if ($results['status'] == 'finished')
        {
            break;
        }
        # sleep for 15 seconds if the task has the status - 'processing'
        sleep(15);
    }
    print_r($results);
}

curl_close($ch);
                        