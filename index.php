<?php
include('db.php');
include('UserRepository.php');
include('PageHandler.php');

class IndexHandler extends PageHandler {

    public function mainOutput() {
        $repository = $this->getRepository();

        $userCount = $repository->getUserCount();
        $limit = 5;
        if(isset($_GET['page']) && (int) $_GET['page'] > 0) {
            $page = (int) $_GET['page'];
        } else {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        $links = $this->pageLinks($limit, $userCount, $page);
        $users = $repository->getUsers($limit, $offset);
        
        return $links . $this->userTable($users, $page);

    }
    
    protected function userTable(array $users, $page = 1) {
        $table = '<table>
                    <tr>
                        <th>User ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Login</th>
                        <th>Password</th>
                        <th>Action</th>
                    <tr>';

        foreach($users as $user) {
            $table .= "<tr>
                        <td>{$user->userID}</td>
                        <td class=\"firstName\" data-value=\"{$user->first_name}\">{$user->first_name}</td>
                        <td class=\"lastName\" data-value=\"{$user->last_name}\">{$user->last_name}</td>
                        <td class=\"login\" data-value=\"{$user->login}\">{$user->login}</td>
                        <td class=\"password\" data-value=\"{$user->password}\">{$user->password}</td>
                        <td class=\"action\">
                            <span class=\"editButtons\">
                                <button class=\"cancel\">Cancel</buttton>
                                <button type=\"submit\" name=\"editUser\" class=\"editButton\" value=\"{$user->userID}\">Submit</button>
                            </span>
                            <button class=\"showEdit\">Edit</button> /
                            <button type=\"submit\" name=\"deleteUser\" class=\"deleteButton\" value=\"{$user->userID}\">Delete</button>
                        </td>
                    </tr>";
        }
        
         $table .= '</table>';

         return '<form action="index.php?page='.$page.'" method="post">'.$table.'</form>';
    }

    protected function pageLinks($limit, $userCount, $currentPage = 1) {
        if($userCount <= $limit) {
            return '';
        }
    }

    public function handlePost() {

        if(isset($_POST['deleteUser'])) {
            return $this->handleDelete();
        } elseif(isset($_POST['editUser'])) {
            return $this->handleUpdate();
        }
    }

    protected function handleDelete() {
        if($_POST['deleteUser'] == $_SESSION['userID']) {
            $this->errors[] = "You cannot delete yourself!";
            return;
        }

        $repository = $this->getRepository();

        $result = $repository->deleteUser($_POST['deleteUser']);

        if($result === true) {
            return "User #{$_POST['deleteUser']} was deleted.";
        }

        $this->errors[] = $result;
    }

    protected function handleUpdate() {
        $userID = $_POST['editUser'];
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $login = $_POST['login'];
        $password = $_POST['password'];
        
        $repository = $this->getRepository();
        
        $error = $repository->updateUser($userID, $firstName, $lastName, $login, $password);
        
        if(empty($error)) {
            return "User updated successfully!";
        }
        
        $this->errors[] = $error;
    }
}

$handler = new IndexHandler();

$postMessage = !empty($_POST) ? $handler->handlePost() : null;


?>
<!DOCTYPE html>

<html>
<head>
    <title>User Management</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="index.js"></script>
    <style type="text/css">
        .editButtons {
            display: none;
        }
    </style>
</head>

<body>
    <h1>User Management</h1>
    <?php echo $handler->menuHTML(); ?>
    <?php
        $output = $handler->mainOutput();

        if(!empty($postMessage)) {
            echo '<div class="success">'.$postMessage.'</div>';
        }

        echo $handler->errorMessages();
        echo $output;
    ?>
</body>
</html>