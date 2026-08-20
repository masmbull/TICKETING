import axios from 'axios';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.axios = axios;
window.Alpine = Alpine;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// User Management search component - must be available before Alpine initializes
window.userSearch = function(config) {
    return {
        allUsers: config.allUsers,
        allRoles: config.allRoles,
        allDepts: config.allDepts,
        searchQuery: '',
        filterRole: '',
        filterDept: '',
        currentPage: 1,
        itemsPerPage: 15,

        get filteredUsers() {
            return this.allUsers
                .filter(user => {
                    // Search filter (case-insensitive)
                    if (this.searchQuery.trim()) {
                        const query = this.searchQuery.trim().toLowerCase();
                        const matchesName = user.name.toLowerCase().includes(query);
                        const matchesEmail = user.email.toLowerCase().includes(query);
                        const matchesJobTitle = (user.job_title || '').toLowerCase().includes(query);
                        const matchesRole = (user.role?.name || '').toLowerCase().includes(query);
                        
                        if (!matchesName && !matchesEmail && !matchesJobTitle && !matchesRole) {
                            return false;
                        }
                    }

                    // Role filter
                    if (this.filterRole && user.role?.id != this.filterRole) {
                        return false;
                    }

                    // Department filter
                    if (this.filterDept && user.department?.id != this.filterDept) {
                        return false;
                    }

                    return true;
                })
                .sort((a, b) => a.name.localeCompare(b.name)); // A-Z sorting
        },

        get totalPages() {
            return Math.ceil(this.filteredUsers.length / this.itemsPerPage) || 1;
        },

        get paginatedUsers() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredUsers.slice(start, end);
        },

        get totalResults() {
            return this.filteredUsers.length;
        },

        goToPage(page) {
            const pageNum = parseInt(page);
            if (pageNum >= 1 && pageNum <= this.totalPages) {
                this.currentPage = pageNum;
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },

        getRoleClass(role) {
            if (!role) return 'bg-slate-100 text-slate-600 border border-slate-200';
            
            const slug = role.slug || role.name?.toLowerCase();
            return {
                'admin': 'bg-purple-500/10 text-purple-600 border border-purple-200',
                'manager': 'bg-blue-500/10 text-blue-600 border border-blue-200',
                'staff': 'bg-emerald-500/10 text-emerald-600 border border-emerald-200',
            }[slug] || 'bg-slate-100 text-slate-600 border border-slate-200';
        },

        resetFilters() {
            this.searchQuery = '';
            this.filterRole = '';
            this.filterDept = '';
            this.currentPage = 1;
        }
    }
};

Alpine.plugin(collapse);
Alpine.start();
