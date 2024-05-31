<?php
// define('WAIT_BEFORE_RECONNECT_uS', 5000000);
// require_once __DIR__ . '/vendor/autoload.php';
// use PhpAmqpLib\Connection\AMQPStreamConnection;

// $connection = null;

// while(true){
//     try {

//         $connection = new AMQPStreamConnection('localhost', 5672, 'adminuser', 'password');
//         process_message($connection);

//     } catch(AMQPRuntimeException $e) {
//         echo $e->getMessage();
//         cleanup_connection($connection);
//         usleep(WAIT_BEFORE_RECONNECT_uS);
//     } catch(\RuntimeException $e) {
//         echo $e->getMessage();
//         cleanup_connection($connection);
//         usleep(WAIT_BEFORE_RECONNECT_uS);
//     } catch(\ErrorException $e) {
//         echo $e->getMessage();
//         cleanup_connection($connection);
//         usleep(WAIT_BEFORE_RECONNECT_uS);
//     }
// }

// function process_message($connection) {

//     $channel = $connection->channel();

//     $channel->queue_declare('task_queue', false, true, false, false);

//     echo " [*] Waiting for messages. To exit press CTRL+C\n";

//     $callback = function ($msg) {
//         echo ' [x] Received ', $msg->body, "\n";
//         sleep(substr_count($msg->body, '.'));
//         echo " [x] Done\n";
//         $msg->ack();
//     };

//     $channel->basic_qos(null, 1, null);
//     $channel->basic_consume('task_queue', '', false, false, false, false, $callback);

//     while ($channel->is_open()) {
//         $channel->wait();
//     }

//     print_r("closing now");

//     $channel->close();
//     $connection->close();

// }

function cleanup_connection($connection, $ch) {
    try {
        if($ch){
            // $ch->close();
            $ch = null;
        }
        if($connection){
            // $connection->close();
            $connection = null;
        }
        // not able to connect
    } catch(AMQPRuntimeException $e) {
        echo "\nXXAMQPRuntimeException : ".$e->getMessage()."\n";
    } catch(\RuntimeException $e) {
        echo "XXRuntimeException : ".$e->getMessage()."\n";
    } catch(\ErrorException $e) {
        echo "\nXXErrorException : ".$e->getMessage()."\n";
    }
}



// include(__DIR__ . '/config.php');

require_once __DIR__.'/vendor/autoload.php';

define('HOST', 'localhost');
define('PORT', 5672);
define('USER', 'adminuser');
define('PASS', 'password');
define('VHOST', '/');
define('WAIT_BEFORE_RECONNECT_uS', 50000);

//If this is enabled you can see AMQP output on the CLI
// define('AMQP_DEBUG', true);

use PhpAmqpLib\Connection\AMQPConnection;

$exchange = 'router';
$queue = 'msgs';
$consumer_tag = 'consumer';

$connection = null;
$ch = null;

while(true){
    try {

        print_r("\nConnecting...\n");
        $connection = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
        $ch = $connection->channel();
        $ch->queue_declare($queue, false, true, false, false);
        $ch->exchange_declare($exchange, 'direct', false, true, false);
        $ch->queue_bind($queue, $exchange);
        $ch->basic_consume($queue, $consumer_tag, false, false, false, false, 'process_message');
        while (count($ch->callbacks)) {
            print_r("\nWaiting...\n");
            $ch->wait();
        }  
        // $ch->consume();      

    } catch(AMQPRuntimeException $e) {
        echo "\nAMQPRuntimeException : ".$e->getMessage()."\n";
        cleanup_connection($connection, $ch);
        usleep(WAIT_BEFORE_RECONNECT_uS);
    } catch(\RuntimeException $e) {
        echo "RuntimeException : ".$e->getMessage()."\n";
        cleanup_connection($connection, $ch);
        usleep(WAIT_BEFORE_RECONNECT_uS);
    } catch(\ErrorException $e) {
        echo "ErrorException : ".$e->getMessage()."\n";
        cleanup_connection($connection, $ch);
        usleep(WAIT_BEFORE_RECONNECT_uS);
    }
}


// $conn = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
// $ch = $conn->channel();

// /*
//     name: $queue
//     passive: false
//     durable: true // the queue will survive server restarts
//     exclusive: false // the queue can be accessed in other channels
//     auto_delete: false //the queue won't be deleted once the channel is closed.
// */

// $ch->queue_declare($queue, false, true, false, false);

// /*
//     name: $exchange
//     type: direct
//     passive: false
//     durable: true // the exchange will survive server restarts
//     auto_delete: false //the exchange won't be deleted once the channel is closed.
// */

// $ch->exchange_declare($exchange, 'direct', false, true, false);

// $ch->queue_bind($queue, $exchange);

function process_message($msg)
{
    echo "\n--------\n";
    echo $msg->body;
    echo "\n--------\n";

    $msg->delivery_info['channel']->
        basic_ack($msg->delivery_info['delivery_tag']);

    // Send a message with the string "quit" to cancel the consumer.
    if ($msg->body === 'quit') {
        $msg->delivery_info['channel']->
            basic_cancel($msg->delivery_info['consumer_tag']);
    }
}

// /*
//     queue: Queue from where to get the messages
//     consumer_tag: Consumer identifier
//     no_local: Don't receive messages published by this consumer.
//     no_ack: Tells the server if the consumer will acknowledge the messages.
//     exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
//     nowait:
//     callback: A PHP Callback
// */

// $ch->basic_consume($queue, $consumer_tag, false, false, false, false, 'process_message');

function shutdown($ch, $conn)
{
    $ch->close();
    $conn->close();
}
register_shutdown_function('shutdown', $ch, $conn);

// // Loop as long as the channel has callbacks registered
// while (count($ch->callbacks)) {
//     $ch->wait();
// }

?>