<?php
class UserRepository {
    protected $pdo;

    /**
     * Class to handle DB operations on users table
     *
     * @param PDO     $pdo PDO object to run queries with
     * 
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Gets a user by userID. If the user doesn't exist, it will return null
     *
     * @param int $userID
     *
     * @return object|null   generic object representing user
     */
    public function getUser($userID) {
        $stmt = $this->pdo->prepare("SELECT userID, first_name, last_name, login, password
                                    FROM users
                                    WHERE userID = :userid");

        $stmt->bindValue(":userid", $userID);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /**
     * Gets a list of users. If no limit is given, it will return all users.
     * Otherwise it will return users according to the limit and offset.
     * 
     * @param int|null $limit  Maximum users to return (null means all users)
     * @param int $offset Number to offset results by if limit is provided
     * 
     * @return array    array of generic user objects
     */
    public function getUsers($limit = null, $offset = 0) {
        if(!is_null($limit)) {
            $stmt = $this->pdo->prepare("SELECT userID, first_name, last_name, login, password
                                        FROM users
                                        LIMIT :offset, :limit");
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        } else {
            $stmt = $this->pdo->prepare("SELECT userID, first_name, last_name, login, password
                                        FROM users");
        }
        
        $userList = [];

        //if the statement doesn't execute for some reason, just return an empty list
        if(!$stmt->execute()) {
            return $userList;
        }
        
        while($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $userList[] = $row;
        }
        
        return $userList;
    }

    /**
     * Gets total number of users
     *
     * @return int    total users
     */
    public function getUserCount() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as numUsers FROM users");

        if(!$stmt->execute()) {
            return 0;
        }

        $row = $stmt->fetch();
        return $row['numUsers'];
    }

    /**
     * Gets the userID based on matching login and password. If they don't match,
     * then the function returns null.
     *
     * NOTE: password is not hashed.
     *
     * @param string $login
     * @param string $password
     *
     * @return int|null    userID or null if login and password doesn't match
     */
    public function checkUser($login, $password) {
        $stmt = $this->pdo->prepare("SELECT userID
                                    FROM users
                                    WHERE login = :login
                                    AND password = :password");

        $stmt->bindValue(':login', $login);
        $stmt->bindValue(':password', $password);

        if(!$stmt->execute()) {
            return null;
        }

        if($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            return $row->userID;
        }

        return null;
    }

    /**
     * Makes sure that input values aren't empty and that the login is unique
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $login
     * @param string $password
     * @param int $userID    optional. if included, it will not produce an error message if the given login belongs
     *                       to the specified user
     *
     * @return string|null    Returns an error message or null if everything is fine.
     */
    protected function validateInput($firstName, $lastName, $login, $password, $userID = null) {
        $errorArray = [];

        if(!empty($userID) && ((int) $userID != $userID || $userID < 0)) {
            return 'userID must be a positive integer.';
        }

        if(empty($firstName) || empty($lastName) || empty($login) || empty($password)) {
            return 'One or more fields are empty.';
        }

        //check to see if login is already taken
        if(!empty($userID)) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as login_exists
                                        FROM users
                                        WHERE login = :login
                                        AND userID != :userid");
            $stmt->bindValue(':userid', $userID);
        } else {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as login_exists
                                        FROM users
                                        WHERE login = :login");
        }

        $stmt->bindValue(':login', $login);

        if(!$stmt->execute()) {
            return 'Problem with database.';
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!empty($row['login_exists'])) {
            return 'Login already exists.';
        }
        
        return null;
    }

    /**
     * Insert a user into the database.
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $login
     * @param string $password
     *
     * @return int|string   inserted userID or error message
     */
    public function insertUser($firstName, $lastName, $login, $password) {
        if($error = $this->validateInput($firstName, $lastName, $login, $password)) {
            return $error;
        }

        $stmt = $this->pdo->prepare("INSERT INTO users(first_name, last_name, login, password)
                                    VALUES(:firstname, :lastname, :login, :password)");
        $stmt->bindValue(':firstname', $firstName);
        $stmt->bindValue(':lastname', $lastName);
        $stmt->bindValue(':login', $login);
        
        /*
         * The password is not hashed because the example seemed to imply
         * that it should be stored in plain text.
         */
        $stmt->bindValue(':password', $password);

        if($stmt->execute()) {
            return $this->pdo->lastInsertId();
        }

        return 'Problem with database.';
    }

    /**
     * Updates a user with specified userId
     *
     * @param int $userID
     * @param string $firstName
     * @param string $lastName
     * @param string $login
     * @param string $password
     *
     * @return string|null    returns null if it was successful or returns an error message.
     */
    public function updateUser($userID, $firstName, $lastName, $login, $password) {

        if($error = $this->validateInput($firstName, $lastName, $login, $password, $userID)) {
            return $error;
        }

        $stmt = $this->pdo->prepare("UPDATE users
                                    SET first_name = :firstname,
                                    last_name = :lastname,
                                    login = :login,
                                    password = :password
                                    WHERE userID = :userid");

        $stmt->bindValue(':userid', $userID);
        $stmt->bindValue(':firstname', $firstName);
        $stmt->bindValue(':lastname', $lastName);
        $stmt->bindValue(':login', $login);

        /*
         * The password is not hashed because the example seemed to imply
         * that it should be stored in plain text.
         */
        $stmt->bindValue(':password', $password);

        if(!$stmt->execute()) {
            return 'Problem with database';
        }

        return null;
    }

    /**
     * Deletes a user with specified id
     *
     * @param int $userID
     *
     * @return bool|string    Returns true if it was successful or an error message
     */
    public function deleteUser($userID) {
        $stmt = $this->pdo->prepare("DELETE FROM users
                                    WHERE userID = :userid");

        $stmt->bindValue(':userid', $userID);

        if(!$stmt->execute()) {
            return 'Problem deleting user.';
        }

        if(empty($stmt->rowCount())) {
            return 'User didn\'nt exist.';
        }

        return true;
    }
}