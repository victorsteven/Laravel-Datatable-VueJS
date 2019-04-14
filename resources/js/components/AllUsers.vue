<template>
    <div class="users-style">
        <div style="margin-bottom: 20px;">
            <h2>Laravel and VueJS Datatable from Scratch</h2>
        </div>
        <div class="table-style">
            <input class="input" type="text" v-model="search" placeholder="Search..."
                   @input="resetPagination()" style="width: 250px;">
            <div class="control">
                <div class="select">
                    <select v-model="length" @change="resetPagination()">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                    </select>
                </div>
            </div>
        </div>
        <table class="table table-bordered table-responsive">
            <thead>
                <tr>
                    <th v-for="column in columns" :key="column.name" @click="sortBy(column.name)"
                        :class="sortKey === column.name ? (sortOrders[column.name] > 0 ? 'sorting_asc' : 'sorting_desc') : 'sorting'"
                        style="width: 40%; cursor:pointer;">
                        {{column.label}}
                    </th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in paginatedUsers" :key="user.id">
                    <td>{{user.name}}</td>
                    <td>{{user.email}}</td>
                    <td>{{user.created_at}}</td>
                    <td><a class="btn btn-danger btn-sm" href="#" @click="deleteUser(user.id)">Remove</a></td>
                </tr>
            </tbody>
        </table>
        <div>
            <nav class="pagination" v-if="!tableShow.showdata">
                <span class="page-stats">{{pagination.from}} - {{pagination.to}} of {{pagination.total}}</span>
                <a v-if="pagination.prevPageUrl" class="btn btn-sm btn-primary pagination-previous" @click="--pagination.currentPage">
                    Prev
                </a>
                <a class="btn btn-sm btn-primary pagination-previous" v-else disabled>
                Prev
                </a>
                <a v-if="pagination.nextPageUrl" class="btn btn-sm pagination-next" @click="++pagination.currentPage">
                    Next
                </a>
                <a class="btn btn-sm btn-primary pagination-next" v-else disabled>
                    Next
                </a>
            </nav>
            <nav class="pagination" v-else>
                <span class="page-stats">
                    {{pagination.from}} - {{pagination.to}} of {{filteredUsers.length}}
                    <span v-if="`filteredUsers.length < pagination.total`"></span>
                </span>
                <a v-if="pagination.prevPage" class="btn btn-sm btn-primary pagination-previous" @click="--pagination.currentPage">
                    Prev
                </a>
                <a class="btn btn-sm pagination-previous btn-primary" v-else disabled>
                Prev
                </a>
                <a v-if="pagination.nextPage" class="btn btn-sm btn-primary pagination-next" @click="++pagination.currentPage">
                    Next
                </a>
                <a class="btn btn-sm pagination-next btn-primary"  v-else disabled>
                    Next
                </a>
            </nav>
        </div>
    </div>
</template>

<script>

export default {
    created() {
        this.getUsers();

        Fire.$on('reloadUsers', () => {
            this.getUsers();
        })
    },

    data() {
        let sortOrders = {};
        let columns = [
            {label: 'Name', name: 'name' },
            {label: 'Email', name: 'email'},
            {label: 'Date Added', name: 'created_at'},
        ];
        columns.forEach((column) => {
           sortOrders[column.name] = -1;
        });
        
        return {
            users: [],
            columns: columns,
            sortKey: 'created_at',
            sortOrders: sortOrders,
            length: 10,
            search: '',
            tableShow: {
                showdata: true,
            },
            pagination: {
                currentPage: 1,
                total: '',
                nextPage: '',
                prevPage: '',
                from: '',
                to: ''
            },
        }
    },

    methods: {
        // deleteUser(id) {
        //     axios.delete(`/users/${id}/delete`).then(() => {
        //         Fire.$emit('reloadUsers')
        //         swal(
        //             'Success!',
        //             'User deleted',
        //             'success'
        //         )
        //     }).catch(() => {
        //         swal('Failed', 'There was something wrong', 'warning');
        //     });
        // },
        deleteUser(id){
            swal({
                title: 'Delete this user?',
                text: "You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {
                        axios.delete(`/users/${id}/delete`).then(()=>{
                            Fire.$emit('reloadUsers')
                                swal(
                                'Deleted!',
                                'User Deleted.',
                                'success'
                                )
                            Fire.$emit('AfterCreate');
                        }).catch(()=> {
                            swal("Failed!", "There was something wronge.", "warning");
                        });
                    }
                })
        },
        
        getUsers() {
            axios.get('/users/', {params: this.tableShow})
                .then(response => {
                    console.log('The data: ', response.data)
                    this.users = response.data;
                    this.pagination.total = this.users.length;
                })
                .catch(errors => {
                    console.log(errors);
                });
        },
        paginate(array, length, pageNumber) {
            this.pagination.from = array.length ? ((pageNumber - 1) * length) + 1 : ' ';
            this.pagination.to = pageNumber * length > array.length ? array.length : pageNumber * length;
            this.pagination.prevPage = pageNumber > 1 ? pageNumber : '';
            this.pagination.nextPage = array.length > this.pagination.to ? pageNumber + 1 : '';
            return array.slice((pageNumber - 1) * length, pageNumber * length);
        },
        resetPagination() {
            this.pagination.currentPage = 1;
            this.pagination.prevPage = '';
            this.pagination.nextPage = '';
        },
        sortBy(key) {
            this.resetPagination();
            this.sortKey = key;
            this.sortOrders[key] = this.sortOrders[key] * -1;
        },
        getIndex(array, key, value) {
            return array.findIndex(i => i[key] == value)
        },
    },
    computed: {

        filteredUsers() {
            let users = this.users;
            if (this.search) {
                users = users.filter((row) => {
                    return Object.keys(row).some((key) => {
                        return String(row[key]).toLowerCase().indexOf(this.search.toLowerCase()) > -1;
                    })
                });
            }
            let sortKey = this.sortKey;
            let order = this.sortOrders[sortKey] || 1;
            if (sortKey) {
                users = users.slice().sort((a, b) => {
                    let index = this.getIndex(this.columns, 'name', sortKey);
                    a = String(a[sortKey]).toLowerCase();
                    b = String(b[sortKey]).toLowerCase();
                    if (this.columns[index].type && this.columns[index].type === 'date') {
                        return (a === b ? 0 : new Date(a).getTime() > new Date(b).getTime() ? 1 : -1) * order;
                    } else if (this.columns[index].type && this.columns[index].type === 'number') {
                        return (+a === +b ? 0 : +a > +b ? 1 : -1) * order;
                    } else {
                        return (a === b ? 0 : a > b ? 1 : -1) * order;
                    }
                });
            }
            return users;
        },
        paginatedUsers() {
            return this.paginate(this.filteredUsers, this.length, this.pagination.currentPage);
        }
    }
};
</script>


