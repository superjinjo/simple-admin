<?php
include('config.php');
include('UserRepository.php');
include('PageHandler.php');

class IndexHandler extends PageHandler {

    public function mainOutput() {
        $repository = $this->getRepository();

        $userCount = $repository->getUserCount();

        if(isset($_GET['page']) && (int) $_GET['page'] > 0) {
            $page = (int) $_GET['page'];
        } else {
            $page = 1;
        }

        $offset = ($page - 1) * PAGE_LIMIT;

        $links = $this->pageLinks(PAGE_LIMIT, $userCount, $page);
        $users = $repository->getUsers(PAGE_LIMIT, $offset);
        
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
                            <span class=\"editButtons\" data-value=\"{$user->userID}\">
                                <button type=\"submit\" name=\"editUser\" class=\"editButton\" value=\"{$user->userID}\">Submit</button>
                                <button class=\"cancel\">Cancel</button>
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

        $output = '<div class="pagination">';

        $pages = ceil($userCount / $limit);

        for($i = 1; $i <= $pages; $i++) {
            if($i == $currentPage) {
                $output .= '<span class="currentPage">'.$i.'</span> ';
            } else {
                $output .= '<a href="index.php?page='.$i.'">'.$i.'</a> ';
            }
        }

        return trim($output) . '</div>';
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