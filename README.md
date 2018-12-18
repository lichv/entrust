# entrust
Role-based Permissions for Laravel 5

fork https://github.com/Zizaco/entrust 
扩展RBAC，添加用户分组部分，用户和用户分组同事具有角色，多对多关系，分组可用于部门管理或团队管理

# 用法
```
$user1 = new User();
$user1->name         = 'lichv';
$user1->password      = Hash::make('lichv');
$user1->email = 'lichvy@126.com';
$user1->save();

$user2 = new User();
$user2->name         = 'ssvain';
$user2->password      = Hash::make('ssvain');
$user2->email = 'ssvain@126.com';
$user2->save();

$group1 = new Group();
$group1->name         = 'admin';
$group1->display_name = '管理员';
$group1->save();

$group2 = new Group();
$group2->name         = 'operator';
$group2->display_name = '运营部门';
$group2->save();


$role1 = new Role();
$role1->name         = 'sys_manager';
$role1->display_name = '普通后台用户';
$role1->description  = 'User is the owner of a given project';
$role1->save();

$role2 = new Role();
$role2->name         = 'sys_admin';
$role2->display_name = '后台管理员'; // optional
$role2->description  = 'User is allowed to manage and edit other users'; // optional
$role2->save();

$role3 = new Role();
$role3->name         = 'department_leader';
$role3->display_name = '部门leader'; // optional
$role3->description  = 'User is allowed to manage and edit other users'; // optional
$role3->save();

$createPost = new Permission();
$createPost->name         = 'create-post';
$createPost->display_name = 'Create Posts';
$createPost->description  = 'create new blog posts';
$createPost->save();

$editPost = new Permission();
$editPost->name         = 'edit-post';
$editPost->display_name = 'edit Posts';
$editPost->description  = 'edit blog posts';
$editPost->save();

$createUser = new Permission();
$createUser->name         = 'create-user';
$createUser->display_name = 'create Users';
$createUser->description  = 'create existing users';
$createUser->save();

$editUser = new Permission();
$editUser->name         = 'edit-user';
$editUser->display_name = 'Edit Users';
$editUser->description  = 'edit existing users';
$editUser->save();

$role1->attachPermission([$createPost,$editPost,$createUser]);
$role2->attachPermission([$createPost,$createUser]);
$role3->attachPermission([$createUser,$editUser]);

$group1->attachRole([$role1,$role2]);
$group2->attachRole([$role2,$role3]);

$user1->attachRole($role3);
$user1->attachGroup($group1);
    
$user1->can('edit-post');
$user1->can('edit-user');
```
