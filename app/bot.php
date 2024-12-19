<?php

include 'functions.php';
date_default_timezone_set("Asia/Jakarta");
$now = strtotime(date("Y-m-d H:i:s"));
$deadline = strtotime('2024-12-18 15:40:00');

$pdo = pdo_connect();
$path = bot_token_path();

// Ambil update menggunakan getUpdates
$updates = json_decode(file_get_contents("https://api.telegram.org/bot" . BOT_TOKEN . "/getUpdates"), true);

if ($updates['ok']) {
    foreach ($updates['result'] as $update) {
        $chatId = $update["message"]["chat"]["id"];
        $message = $update["message"]["text"];
        $message_id = $update["message"]["message_id"];
        
        // Log pesan yang diterima
        $stmt3 = $pdo->prepare("INSERT INTO tb_log(id_chat, command_log) VALUES(?,?)");
        $stmt3->execute([$chatId, $message]);

        // Cek status
        $stmt_status = $pdo->prepare('SELECT * FROM tb_status WHERE id_chat = ?');
        $stmt_status->execute([$chatId]);
        $arr_status = $stmt_status->fetch(PDO::FETCH_ASSOC);

        $my_status = $arr_status['status'];
        $my_status_opt = $arr_status['status_option'];
        $my_status_opt2 = $arr_status['status_option2'];

        if ($now > $deadline) {
            // Kirim pesan jika waktu habis
            file_get_contents($path."/sendmessage?chat_id=".$chatId."&text=Waktu habis.");
        } else {
            // Cek jika status sedang menunggu input dari user
            if ($my_status == "waiting_name" || $my_status == "waiting_answer") {
                if ($message) {
                    // Proses jika status adalah waiting_name
                    if ($my_status == "waiting_name") {
                        // Simpan nama dan lanjutkan
                        $stmt_del_stat = $pdo->prepare("DELETE FROM tb_status WHERE id_chat = ?");
                        $stmt_del_stat->execute([$chatId]);
                        $stmt_ins_name = $pdo->prepare("INSERT INTO tb_user(id_chat, name) VALUES(?,?)");
                        $stmt_ins_name->execute([$chatId, $message]);

                        // Kirim keyboard dan pesan selamat datang
                        $keyboard = array(array("Pertanyaan"));
                        $resp = array("keyboard" => $keyboard, "resize_keyboard" => true, "one_time_keyboard" => true);
                        $reply = json_encode($resp);
                        file_get_contents($path."/sendmessage?chat_id=".$chatId."&text=Selamat datang ".$message."&reply_markup=".$reply);
                        break;
                    }

                    // Proses jika status adalah waiting_answer
                    if ($my_status == "waiting_answer") {
                        // Proses jawaban dan simpan
                        $message = strtoupper($message);
                        $correct_answer = array("A", "B", "C", "D");
                        if (in_array($message, $correct_answer)) {
                            $stmt_del_stat = $pdo->prepare("DELETE FROM tb_status WHERE id_chat = ?");
                            $stmt_del_stat->execute([$chatId]);

                            // Simpan jawaban ke database
                            $stmt_save_ans = $pdo->prepare("INSERT INTO tb_answer(id_chat, no_question, id_question, answer, correct_answer) VALUES(?,?,?,?,?)");
                            $stmt_save_ans->execute([$chatId, $next_number, $my_status_opt, $message, $my_status_opt2]);

                            // Kirim pesan hasil jawaban
                            $exam_message = $choose['question'];
                            $keyboard = array(array("Pertanyaan"));
                            $resp = array("keyboard" => $keyboard, "resize_keyboard" => true, "one_time_keyboard" => true);
                            $reply = json_encode($resp);
                            file_get_contents($path."/sendmessage?chat_id=".$chatId."&text=Jawaban Anda : ".$message.", untuk pertanyaan : ".urlencode($exam_message)."&reply_markup=".$reply);
                            break;
                        } else {
                            // Jika jawaban tidak valid, minta jawaban lagi
                            $keyboard = array(array("Pertanyaan"));
                            $resp = array("keyboard" => $keyboard, "resize_keyboard" => true, "one_time_keyboard" => true);
                            $reply = json_encode($resp);
                            file_get_contents($path."/sendmessage?chat_id=".$chatId."&text=Jawaban tidak dikenali&reply_markup=".$reply);
                            break;
                        }
                    }
                } else {
                    // Jika tidak ada input, terus meminta input
                    file_get_contents($path."/sendmessage?chat_id=".$chatId."&text=Silakan masukkan jawaban atau nama Anda.");
                    break;
                }
            }

            // Jika status bukan waiting_name atau waiting_answer
            if ($my_status != "waiting_name" && $my_status != "waiting_answer") {
                if (isset($message) && $message == "/start") {
                    // cek apakah sudah pernah start dan mengisi nama
                    $stmt_cek = $pdo->prepare('SELECT * FROM tb_user WHERE id_chat = ?');
                    $stmt_cek->execute([$chatId]);
                    $cek_user = $stmt_cek->fetchColumn();

                    if ($cek_user == 0) {
                        $welcome_message1 = "Selamat Datang, ID Anda : ". $chatId;
                        $welcome_message2 = "Tuliskan nama Anda ";

                        $welcome_message = urlencode("$welcome_message1 \n$welcome_message2");
                        // Simpan status waiting untuk input nama
                        $stmt_wait_name = $pdo->prepare("INSERT INTO tb_status(id_chat, status) VALUES(?, ?)");
                        $stmt_wait_name->execute([$chatId, 'waiting_name']);
                        file_get_contents($path."/sendmessage?chat_id=".$chatId."&text=".$welcome_message);
                    } else {
                        file_get_contents($path."/sendmessage?chat_id=".$chatId."&text=Perintah telah dijalankan");
                    }
                }
            }
        }
    }
}
?>
