<?php
// define('WAIT_BEFORE_RECONNECT_uS', 5000000);
// require_once __DIR__ . '/vendor/autoload.php';
// use PhpAmqpLib\Connection\AMQPStreamConnection;
// use PhpAmqpLib\Message\AMQPMessage;

// $connection = null;

// while(true){
//     try {

//         $connection = new AMQPStreamConnection('localhost', 5672, 'adminuser', 'password');
//         send_message($connection);

//     } catch(AMQPRuntimeException $e) {
//         echo $e->getMessage();
//         cleanup_connection($connection, $argv);
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

// function send_message($connection, $argv) {

//     $channel = $connection->channel();

//     $channel->queue_declare('task_queue', false, true, false, false);

//     $data = implode(' ', array_slice($argv, 1));
//     if (empty($data)) {
//         $data = "Hello World!";
//     }
//     $msg = new AMQPMessage(
//         $data,
//         array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
//     );

//     $channel->basic_publish($msg, '', 'task_queue');

//     echo ' [x] Sent ', $data, "\n";

//     $channel->close();
//     $connection->close();

// }

// function cleanup_connection($connection) {
//     $connection = null;
//     // not able to connect
// }



// require_once __DIR__ . '/vendor/autoload.php';
// use PhpAmqpLib\Connection\AMQPStreamConnection;
// use PhpAmqpLib\Message\AMQPMessage;

// $connection = new AMQPStreamConnection('localhost', 5672, 'adminuser', 'password');
// $channel = $connection->channel();

// $channel->queue_declare('task_queue', false, true, false, false);

// $data = implode(' ', array_slice($argv, 1));
// if (empty($data)) {
//     $data = "Hello World!";
// }
// // AMQPMessage::DELIVERY_MODE_PERSISTENT
// $msg = new AMQPMessage(
//     $data,
//     array('delivery_mode' => 1)
// );

// $channel->basic_publish($msg, '', 'task_queue');

// echo ' [x] Sent ', $data, "\n";

// $channel->close();
// $connection->close();


// include(__DIR__ . '/config.php');


require_once __DIR__.'/vendor/autoload.php';

define('HOST', 'localhost');
define('PORT', 5672);
define('USER', 'adminuser');
define('PASS', 'password');
define('VHOST', '/');

//If this is enabled you can see AMQP output on the CLI
// define('AMQP_DEBUG', true);

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$exchange = 'router';
$queue = 'msgs';

$conn = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
$ch = $conn->channel();

/*
    name: $queue
    passive: false
    durable: true // the queue will survive server restarts
    exclusive: false // the queue can be accessed in other channels
    auto_delete: false //the queue won't be deleted once the channel is closed.
*/

$ch->queue_declare($queue, false, true, false, false);

/*
    name: $exchange
    type: direct
    passive: false
    durable: true // the exchange will survive server restarts
    auto_delete: false //the exchange won't be deleted once the channel is closed.
*/

$ch->exchange_declare($exchange, 'direct', false, true, false);

$ch->queue_bind($queue, $exchange);

$msg_body = implode(' ', array_slice($argv, 1));
$msg = new AMQPMessage($msg_body, array('content_type' => 'text/plain', 'delivery_mode' => 2));
$ch->basic_publish($msg, $exchange);

$ch->close();
$conn->close();


?>