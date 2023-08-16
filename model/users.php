<?php
/**
 * Model file for all user table transactions
 *
 * @author Vanessa Richie Alia-Trapero <vrat.engr@gmail.com>
 */

declare(strict_types = 1);

class usersModel
{
    public $db;
    public $data;

    public function __construct() {
        global $config;
        extract($config->database);
        $this->db = new mysqli($host, $user, $password, $db);
        if ($this->db->connect_error) {
            die("Error in connection: " . $this->db->connect_error);
        }
    }

    /**
     * save user data from registration
     *
     * @param   array $data     inputted user data
     * @return  array $user     database user data
     */
    public function save(array $data) : array {
        if ($data['auth'] == 'email') {
            // if this is an email registration, we mwill encrypt user's password first before saving
            $password = sha1($data['password']);
            $info = '';
            $stmt = $this->db->prepare("INSERT INTO users (auth, email, password, name, info) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $data['auth'], $data['email'], $password, $data['name'], $info);

        } else {
            // if this is an oauth registration, user does not enter a password in our form since the authentication is done on the third-party site, so no password encryption
            // instead, we'll save additional details returned by the third-party site
            unset($data['token']);
            $password = '';
            $info = json_encode($data);
            $stmt = $this->db->prepare("INSERT INTO users (auth, email, name, password, info) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $data['auth'], $data['email'], $data['name'], $password, $info);
        }
        $stmt->execute();

        // let's return the user data that was just saved from the database
        $result = $this->db->query('SELECT * FROM users WHERE id = ' . $this->db->insert_id);
        $user = $result->fetch_assoc();
        return $user;
    }

    /**
     * check if user exists in our system - used in login
     *
     * @param   array $data     inputted user data
     * @return  array $user     database user data
     */
    public function get(array $data) : ?array {
        if ($data['auth'] == 'email') {
            $password = sha1($data['password']);
            $stmt = $this->db->prepare('SELECT * FROM users where email = ? AND password = ? AND auth = ?');
            $stmt->bind_param('sss', $data['email'], $password, $data['auth']);

        } else {
            $stmt = $this->db->prepare('SELECT * FROM users where email = ? AND auth = ?');
            $stmt->bind_param('ss', $data['email'], $data['auth']);
        }
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        return $user;
    }

    /**
     * check if email exists in our system
     * we're using email address as the unique identifier for our users
     * and since we are offering multiple ways to login/register, we want to avoid multi-platform accounts for the same email address
     * this will do a quick check of the email address and return it's linked auth type if there's any
     *
     * @param   string $email       email to be checked
     * @return  string $auth        auth type linked to the email address, null if non found
     */
    public function getAuth(string $email) : ?string {
        $stmt = $this->db->prepare('SELECT auth FROM users where email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($auth);
        $stmt->fetch();
        return $auth;
    }

    /**
     * delete user account
     *
     * @param   string $email       email address of user to be deleted
     */
    public function delete(string $email) : void {
        $stmt = $this->db->prepare('DELETE FROM users where email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
    }

    /**
     * close db connection
     */
    public function close() : void {
        $this->db->close();
    }
 }