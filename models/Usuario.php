<?php

class Usuario {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {

        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password_hash'])) {
                return $user; // login correcto
            }
        }

        return null; // login incorrecto
    }
}