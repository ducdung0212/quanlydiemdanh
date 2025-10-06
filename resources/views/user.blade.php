<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
     <div class="container p-24">
        <h2>Users</h2>

        <div id="user-form">
            <input id="name" placeholder="Name">
            <input id="email" placeholder="Email">
            <input id="password" placeholder="Password" type="password">
            <button id="create">Create</button>
        </div>

        <div id="users"></div>
    </div>

    @push('scripts')
        <script>
            const apiBase = '/api/users';

            async function listUsers(){
                const res = await fetch(apiBase);
                const json = await res.json();
                const container = document.getElementById('users');
                container.innerHTML = '';
                if(json.success && json.data && json.data.data){
                    json.data.data.forEach(u => {
                        const div = document.createElement('div');
                        div.innerHTML = `${u.id} - ${u.name} (${u.email}) <button data-id="${u.id}" class="del">Delete</button>`;
                        container.appendChild(div);
                    });
                    document.querySelectorAll('.del').forEach(btn => btn.addEventListener('click', async e=>{
                        const id = e.target.dataset.id;
                        await fetch(`${apiBase}/${id}`, { method: 'DELETE' });
                        listUsers();
                    }));
                } else {
                    container.innerText = 'No users';
                }
            }

            document.getElementById('create').addEventListener('click', async ()=>{
                const name = document.getElementById('name').value;
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                await fetch(apiBase, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, email, password })
                });
                listUsers();
            });

            listUsers();
        </script>
</body>
</html>