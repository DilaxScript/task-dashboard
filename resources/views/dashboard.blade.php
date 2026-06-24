<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Taskflow — Daily Task Dashboard</title>

        <script>
            (() => {
                const storedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', storedTheme ? storedTheme === 'dark' : prefersDark);
            })();
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f5f7fb] text-slate-950 antialiased transition-colors duration-300 dark:bg-slate-950 dark:text-slate-100">
        <div
            x-data="taskDashboard(@js($tasks), @js($statuses))"
            @keydown.escape.window="closeModal()"
            class="relative min-h-screen overflow-hidden"
        >
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[28rem] overflow-hidden bg-slate-950 dark:bg-black">
                <div class="absolute -left-24 -top-32 h-96 w-96 rounded-full bg-violet-500/20 blur-3xl"></div>
                <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-sky-400/10 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.025]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 24px 24px;"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 pb-16 pt-5 sm:px-6 lg:px-8">
                <header class="flex items-center justify-between border-b border-white/10 pb-5">
                    <a href="/" class="flex items-center gap-3 text-white">
                        <span class="grid size-10 place-items-center rounded-xl bg-white text-slate-950 shadow-lg shadow-black/20">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m7 12 3 3 7-7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10Z" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-lg font-semibold tracking-tight">Taskflow</p>
                            <p class="text-xs text-slate-400">Daily workspace</p>
                        </div>
                    </a>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="toggleTheme($event)"
                            class="grid size-10 place-items-center rounded-xl border border-white/15 bg-white/10 text-white backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/20 focus-visible:ring-2 focus-visible:ring-violet-400"
                            :aria-label="isDark ? 'Switch to light theme' : 'Switch to dark theme'"
                            :title="isDark ? 'Light theme' : 'Dark theme'"
                        >
                            <svg x-show="!isDark" class="size-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5 8.5 8.5 0 1 0 20.5 14.2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <svg x-show="isDark" x-cloak class="size-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </button>

                        <button
                            type="button"
                            @click="openModal()"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950"
                        >
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span class="hidden sm:inline">Add new task</span>
                            <span class="sm:hidden">Add task</span>
                        </button>
                    </div>
                </header>

                <main>
                    <section class="pb-8 pt-12 text-white sm:pt-16">
                        <div class="max-w-2xl">
                            <p class="mb-3 flex items-center gap-2 text-sm font-medium text-violet-300">
                                <span class="size-1.5 rounded-full bg-violet-400"></span>
                                Your daily command center
                            </p>
                            <h1 class="text-4xl font-semibold tracking-[-0.04em] sm:text-5xl">
                                Make today count.
                            </h1>
                            <p class="mt-4 max-w-xl text-sm leading-6 text-slate-400 sm:text-base">
                                Capture what matters, move work forward, and keep every priority visible.
                            </p>
                        </div>
                    </section>

                    <section class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                        <button
                            type="button"
                            @click="statusFilter = 'All'"
                            :class="statusFilter === 'All' ? 'ring-2 ring-violet-500 ring-offset-2' : ''"
                            class="rounded-2xl border border-white/70 bg-white p-4 text-left shadow-[0_12px_36px_rgba(15,23,42,0.08)] transition hover:-translate-y-0.5 dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/20 sm:p-5"
                        >
                            <div class="flex items-center justify-between">
                                <span class="grid size-9 place-items-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span class="text-xs font-medium text-slate-400 dark:text-slate-500">Total</span>
                            </div>
                            <p class="mt-5 text-3xl font-semibold tracking-tight" x-text="tasks.length"></p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">All tasks</p>
                        </button>

                        <template x-for="(status, index) in statuses" :key="status">
                            <button
                                type="button"
                                @click="statusFilter = status"
                                :class="statusFilter === status ? 'ring-2 ring-violet-500 ring-offset-2' : ''"
                                class="rounded-2xl border border-white/70 bg-white p-4 text-left shadow-[0_12px_36px_rgba(15,23,42,0.08)] transition hover:-translate-y-0.5 dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/20 sm:p-5"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="grid size-9 place-items-center rounded-xl"
                                        :class="{
                                            'bg-amber-50 text-amber-600': status === 'Pending',
                                            'bg-sky-50 text-sky-600': status === 'In Progress',
                                            'bg-emerald-50 text-emerald-600': status === 'Completed',
                                        }"
                                    >
                                        <span class="size-2 rounded-full" :class="statusDotClasses(status)"></span>
                                    </span>
                                    <span class="text-xs font-medium text-slate-400" x-text="String(index + 1).padStart(2, '0')"></span>
                                </div>
                                <p class="mt-5 text-3xl font-semibold tracking-tight" x-text="countByStatus(status)"></p>
                                <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400" x-text="status"></p>
                            </button>
                        </template>
                    </section>

                    <section class="mt-6 rounded-3xl border border-slate-200/80 bg-white p-4 shadow-[0_20px_50px_rgba(15,23,42,0.06)] transition-colors dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20 sm:p-6">
                        <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 dark:border-slate-800 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-lg font-semibold tracking-tight">Your tasks</h2>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-300" x-text="filteredTasks.length"></span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage progress without leaving the dashboard.</p>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row">
                                <label class="relative block sm:w-72">
                                    <span class="sr-only">Search tasks</span>
                                    <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                                        <path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <input
                                        x-model.debounce.200ms="search"
                                        type="search"
                                        placeholder="Search by task title..."
                                        class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 transition placeholder:text-slate-400 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-violet-500 dark:focus:bg-slate-800 dark:focus:ring-violet-500/15"
                                    >
                                </label>

                                <label class="relative block">
                                    <span class="sr-only">Filter by status</span>
                                    <select
                                        x-model="statusFilter"
                                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white pl-4 pr-10 text-sm font-medium text-slate-700 transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-violet-500 dark:focus:ring-violet-500/15 sm:w-40"
                                    >
                                        <option value="All">All statuses</option>
                                        <template x-for="status in statuses" :key="status">
                                            <option :value="status" x-text="status"></option>
                                        </template>
                                    </select>
                                    <svg class="pointer-events-none absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="m7 10 5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </label>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            <template x-for="task in filteredTasks" :key="task.id">
                                <article class="group flex min-h-64 flex-col rounded-2xl border border-slate-200 bg-white p-5 transition duration-200 hover:-translate-y-1 hover:border-slate-300 hover:shadow-xl hover:shadow-slate-200/60 dark:border-slate-700 dark:bg-slate-950/60 dark:hover:border-slate-600 dark:hover:shadow-black/30">
                                    <div class="flex items-start justify-between gap-3">
                                        <span
                                            class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-[11px] font-semibold"
                                            :class="statusClasses(task.status)"
                                        >
                                            <span class="size-1.5 rounded-full" :class="statusDotClasses(task.status)"></span>
                                            <span x-text="task.status"></span>
                                        </span>
                                        <div class="flex items-center gap-1">
                                            <button
                                                type="button"
                                                @click="editTask(task)"
                                                class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-violet-50 hover:text-violet-600 focus-visible:ring-2 focus-visible:ring-violet-400"
                                                :aria-label="`Edit ${task.title}`"
                                                title="Edit task"
                                            >
                                                <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M13.5 6.5 17.5 10.5M4 20h4l11-11a2.828 2.828 0 0 0-4-4L4 16v4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                            <button
                                                type="button"
                                                @click="deleteTask(task)"
                                                :disabled="deletingTaskId === task.id"
                                                class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 focus-visible:ring-2 focus-visible:ring-rose-400 disabled:cursor-wait disabled:opacity-50"
                                                :aria-label="`Delete ${task.title}`"
                                                title="Delete task"
                                            >
                                                <svg x-show="deletingTaskId !== task.id" class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <svg x-show="deletingTaskId === task.id" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/>
                                                    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-5 flex-1">
                                        <h3 class="text-lg font-semibold leading-6 tracking-tight text-slate-900 dark:text-slate-100" x-text="task.title"></h3>
                                        <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-500 dark:text-slate-400" x-text="task.description"></p>
                                        <p class="mt-3 text-xs text-slate-400 dark:text-slate-500" x-text="`Created ${formatDate(task.created_at)}`"></p>
                                    </div>

                                    <div class="mt-6 border-t border-slate-100 pt-4 dark:border-slate-800">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-xs font-medium text-slate-400">Update status</span>
                                            <span
                                                x-show="updatingTaskId === task.id"
                                                class="flex items-center gap-1.5 text-xs text-violet-600"
                                            >
                                                <svg class="size-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/>
                                                    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                                </svg>
                                                Saving
                                            </span>
                                        </div>
                                        <div class="relative mt-2">
                                            <select
                                                x-model="task.status"
                                                @change="updateStatus(task, $event.target.value)"
                                                :disabled="updatingTaskId === task.id"
                                                class="h-10 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-3 pr-9 text-sm font-medium text-slate-700 transition hover:border-slate-300 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100 disabled:cursor-wait disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-slate-600 dark:focus:border-violet-500 dark:focus:bg-slate-800 dark:focus:ring-violet-500/15"
                                            >
                                                <option value="Pending">Pending</option>
                                                <option value="In Progress">In Progress</option>
                                                <option value="Completed">Completed</option>
                                            </select>
                                            <svg class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="m7 10 5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                    </div>
                                </article>
                            </template>
                        </div>

                        <div x-show="filteredTasks.length === 0" x-cloak class="py-16 text-center">
                            <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M9 11h6M9 15h4M8 3h8l4 4v14H4V3h4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <h3 class="mt-4 font-semibold text-slate-800 dark:text-slate-200">No tasks found</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400" x-text="tasks.length ? 'Try a different search or status filter.' : 'Create your first task to get started.'"></p>
                            <button
                                x-show="tasks.length === 0"
                                type="button"
                                @click="openModal()"
                                class="mt-5 rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                            >
                                Create first task
                            </button>
                        </div>
                    </section>
                </main>
            </div>

            <div
                x-show="modalOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm"
                @click="closeModal()"
                aria-hidden="true"
            ></div>

            <div
                x-show="modalOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-y-6 opacity-0 sm:scale-95"
                x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
                x-transition:leave-end="translate-y-6 opacity-0 sm:scale-95"
                class="fixed inset-x-0 bottom-0 z-50 max-h-[92vh] overflow-y-auto rounded-t-3xl bg-white p-6 shadow-2xl transition-colors dark:bg-slate-900 sm:inset-auto sm:left-1/2 sm:top-1/2 sm:w-full sm:max-w-lg sm:-translate-x-1/2 sm:-translate-y-1/2 sm:rounded-3xl"
                role="dialog"
                aria-modal="true"
                aria-labelledby="new-task-title"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-violet-600"
                            x-text="editingTaskId ? 'Edit task' : 'New task'"
                        ></p>
                        <h2
                            id="new-task-title"
                            class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 dark:text-white"
                            x-text="editingTaskId ? 'Update task details' : 'What needs to get done?'"
                        ></h2>
                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            x-text="editingTaskId ? 'Keep the task details and progress up to date.' : 'Add the details now; update progress anytime.'"
                        ></p>
                    </div>
                    <button
                        type="button"
                        @click="closeModal()"
                        class="grid size-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-800 focus-visible:ring-2 focus-visible:ring-violet-400 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white"
                        aria-label="Close modal"
                    >
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveTask()" class="mt-7 space-y-5">
                    <div>
                        <label for="title" class="text-sm font-semibold text-slate-700 dark:text-slate-300">Task title</label>
                        <input
                            x-ref="titleInput"
                            x-model="form.title"
                            id="title"
                            type="text"
                            maxlength="120"
                            placeholder="e.g. Prepare sprint review"
                            class="mt-2 h-12 w-full rounded-xl border bg-slate-50 px-4 text-sm text-slate-900 transition placeholder:text-slate-400 focus:bg-white focus:ring-4 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:bg-slate-800"
                            :class="errors.title ? 'border-rose-400 focus:border-rose-400 focus:ring-rose-100' : 'border-slate-200 focus:border-violet-400 focus:ring-violet-100'"
                        >
                        <p x-show="errors.title" x-text="errors.title?.[0]" class="mt-1.5 text-xs font-medium text-rose-600"></p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="description" class="text-sm font-semibold text-slate-700 dark:text-slate-300">Description</label>
                            <span class="text-xs text-slate-400" x-text="`${form.description.length}/1000`"></span>
                        </div>
                        <textarea
                            x-model="form.description"
                            id="description"
                            rows="4"
                            maxlength="1000"
                            placeholder="Add context, next steps, or a useful note..."
                            class="mt-2 w-full resize-none rounded-xl border bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-900 transition placeholder:text-slate-400 focus:bg-white focus:ring-4 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:bg-slate-800"
                            :class="errors.description ? 'border-rose-400 focus:border-rose-400 focus:ring-rose-100' : 'border-slate-200 focus:border-violet-400 focus:ring-violet-100'"
                        ></textarea>
                        <p x-show="errors.description" x-text="errors.description?.[0]" class="mt-1.5 text-xs font-medium text-rose-600"></p>
                    </div>

                    <div>
                        <label for="status" class="text-sm font-semibold text-slate-700 dark:text-slate-300">Status</label>
                        <div class="relative mt-2">
                            <select
                                x-model="form.status"
                                id="status"
                                class="h-12 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-4 pr-10 text-sm font-medium text-slate-700 transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-violet-500 dark:focus:bg-slate-800 dark:focus:ring-violet-500/15"
                            >
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                            </select>
                            <svg class="pointer-events-none absolute right-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m7 10 5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <p x-show="errors.status" x-text="errors.status?.[0]" class="mt-1.5 text-xs font-medium text-rose-600"></p>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 dark:border-slate-800 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            @click="closeModal()"
                            :disabled="submitting"
                            class="h-11 rounded-xl border border-slate-200 px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 text-sm font-semibold text-white shadow-lg shadow-slate-950/15 transition hover:-translate-y-0.5 hover:bg-slate-800 disabled:cursor-wait disabled:opacity-60"
                        >
                            <svg x-show="submitting" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/>
                                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            <span
                                x-text="submitting
                                    ? (editingTaskId ? 'Saving changes...' : 'Creating task...')
                                    : (editingTaskId ? 'Save changes' : 'Create task')"
                            ></span>
                        </button>
                    </div>
                </form>
            </div>

            <div
                x-show="toast"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-y-3 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="translate-y-3 opacity-0"
                class="fixed bottom-5 left-1/2 z-[60] w-[calc(100%-2rem)] max-w-sm -translate-x-1/2"
                role="status"
                aria-live="polite"
            >
                <div
                    class="flex items-center gap-3 rounded-2xl border bg-white p-4 shadow-2xl dark:bg-slate-900"
                    :class="toast?.type === 'error' ? 'border-rose-200' : 'border-emerald-200'"
                >
                    <span
                        class="grid size-8 shrink-0 place-items-center rounded-full"
                        :class="toast?.type === 'error' ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600'"
                    >
                        <svg x-show="toast?.type !== 'error'" class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m6 12 4 4 8-8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg x-show="toast?.type === 'error'" class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 8v5M12 16.5h.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </span>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200" x-text="toast?.message"></p>
                </div>
            </div>
        </div>
    </body>
</html>
