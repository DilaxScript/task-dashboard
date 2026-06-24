import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('taskDashboard', (initialTasks, statuses) => ({
    tasks: initialTasks.map((task) => ({
        ...task,
        persistedStatus: task.status,
    })),
    statuses,
    search: '',
    statusFilter: 'All',
    modalOpen: false,
    editingTaskId: null,
    submitting: false,
    updatingTaskId: null,
    deletingTaskId: null,
    toast: null,
    isDark: document.documentElement.classList.contains('dark'),
    errors: {},
    form: {
        title: '',
        description: '',
        status: 'Pending',
    },

    toggleTheme(event) {
        const button = event.currentTarget;
        const { top, left, width, height } = button.getBoundingClientRect();
        const x = left + width / 2;
        const y = top + height / 2;
        const endRadius = Math.hypot(
            Math.max(x, window.innerWidth - x),
            Math.max(y, window.innerHeight - y),
        );

        const applyTheme = () => {
            this.isDark = !this.isDark;
            document.documentElement.classList.toggle('dark', this.isDark);
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
        };

        if (!document.startViewTransition || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            applyTheme();
            return;
        }

        const transition = document.startViewTransition(applyTheme);

        transition.ready.then(() => {
            document.documentElement.animate(
                {
                    clipPath: [
                        `circle(0px at ${x}px ${y}px)`,
                        `circle(${endRadius}px at ${x}px ${y}px)`,
                    ],
                },
                {
                    duration: 500,
                    easing: 'ease-in-out',
                    pseudoElement: '::view-transition-new(root)',
                },
            );
        });
    },

    get filteredTasks() {
        const query = this.search.trim().toLowerCase();

        return this.tasks.filter((task) => {
            const matchesSearch = !query || task.title.toLowerCase().includes(query);
            const matchesStatus = this.statusFilter === 'All' || task.status === this.statusFilter;

            return matchesSearch && matchesStatus;
        });
    },

    countByStatus(status) {
        return this.tasks.filter((task) => task.status === status).length;
    },

    openModal() {
        this.resetForm();
        this.errors = {};
        this.modalOpen = true;
        this.$nextTick(() => this.$refs.titleInput.focus());
    },

    editTask(task) {
        this.editingTaskId = task.id;
        this.form = {
            title: task.title,
            description: task.description,
            status: task.status,
        };
        this.errors = {};
        this.modalOpen = true;
        this.$nextTick(() => this.$refs.titleInput.focus());
    },

    closeModal() {
        if (this.submitting) return;

        this.modalOpen = false;
        this.editingTaskId = null;
        this.errors = {};
    },

    async saveTask() {
        this.submitting = true;
        this.errors = {};

        try {
            const isEditing = this.editingTaskId !== null;
            const response = await fetch(isEditing ? `/tasks/${this.editingTaskId}` : '/tasks', {
                method: isEditing ? 'PUT' : 'POST',
                headers: this.headers(),
                body: JSON.stringify(this.form),
            });
            const data = await response.json();

            if (!response.ok) {
                if (response.status === 422) {
                    this.errors = data.errors ?? {};
                    return;
                }

                throw new Error(data.message || (isEditing ? 'Unable to update the task.' : 'Unable to create the task.'));
            }

            if (isEditing) {
                const task = this.tasks.find((item) => item.id === this.editingTaskId);

                if (task) {
                    Object.assign(task, data.task, {
                        persistedStatus: data.task.status,
                    });
                }
            } else {
                this.tasks.unshift({
                    ...data.task,
                    persistedStatus: data.task.status,
                });
            }

            this.resetForm();
            this.modalOpen = false;
            this.showToast(data.message, 'success');
        } catch (error) {
            this.showToast(error.message || 'Something went wrong.', 'error');
        } finally {
            this.submitting = false;
        }
    },

    async deleteTask(task) {
        if (!window.confirm(`Delete "${task.title}"? This action cannot be undone.`)) {
            return;
        }

        this.deletingTaskId = task.id;

        try {
            const response = await fetch(`/tasks/${task.id}`, {
                method: 'DELETE',
                headers: this.headers(),
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Unable to delete the task.');
            }

            this.tasks = this.tasks.filter((item) => item.id !== task.id);
            this.showToast(data.message, 'success');
        } catch (error) {
            this.showToast(error.message || 'Something went wrong.', 'error');
        } finally {
            this.deletingTaskId = null;
        }
    },

    async updateStatus(task, newStatus) {
        const previousStatus = task.persistedStatus;
        task.status = newStatus;
        this.updatingTaskId = task.id;

        try {
            const response = await fetch(`/tasks/${task.id}/status`, {
                method: 'PATCH',
                headers: this.headers(),
                body: JSON.stringify({ status: newStatus }),
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Unable to update the status.');
            }

            Object.assign(task, data.task);
            task.persistedStatus = data.task.status;
            this.showToast(data.message, 'success');
        } catch (error) {
            task.status = previousStatus;
            this.showToast(error.message || 'Something went wrong.', 'error');
        } finally {
            this.updatingTaskId = null;
        }
    },

    resetForm() {
        this.editingTaskId = null;
        this.form = {
            title: '',
            description: '',
            status: 'Pending',
        };
        this.errors = {};
    },

    headers() {
        return {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        };
    },

    showToast(message, type) {
        this.toast = { message, type };

        window.clearTimeout(this.toastTimer);
        this.toastTimer = window.setTimeout(() => {
            this.toast = null;
        }, 3200);
    },

    statusClasses(status) {
        return {
            Pending: 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300',
            'In Progress': 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-300',
            Completed: 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300',
        }[status];
    },

    statusDotClasses(status) {
        return {
            Pending: 'bg-amber-500',
            'In Progress': 'bg-sky-500',
            Completed: 'bg-emerald-500',
        }[status];
    },

    formatDate(value) {
        return new Intl.DateTimeFormat('en', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        }).format(new Date(value));
    },
}));

Alpine.start();
