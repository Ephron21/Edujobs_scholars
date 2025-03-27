/**
 * Admin Dashboard JavaScript
 * This file contains all interactive functionality for the admin dashboard
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Dashboard
    initTypingEffect();
    initCharts();
    initEventListeners();
    initTaskList();
    initNotifications();
    initFileUpload();
    initCalendar();
    initDarkMode();
});

/**
 * Initialize typing effect for admin name
 */
function initTypingEffect() {
    const adminNameElement = document.getElementById('adminName');
    
    if (adminNameElement && adminNameElement.dataset.name) {
        const adminName = adminNameElement.dataset.name;
        let i = 0;
        const speed = 100; // typing speed
        
        function typeWriter() {
            if (i < adminName.length) {
                adminNameElement.innerHTML += adminName.charAt(i);
                i++;
                setTimeout(typeWriter, speed);
            }
        }
        
        typeWriter();
    }
}

/**
 * Initialize charts using Chart.js
 */
function initCharts() {
    if (typeof Chart === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js';
        script.onload = createCharts;
        document.head.appendChild(script);
    } else {
        createCharts();
    }
}

/**
 * Create charts for dashboard
 */
function createCharts() {
    // User activity chart (line chart)
    const activityCtx = document.getElementById('activityChart');
    if (activityCtx) {
        new Chart(activityCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'New Users',
                    data: [65, 59, 80, 81, 56, 55],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Active Users',
                    data: [28, 48, 40, 19, 86, 27],
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }

    // User types chart (doughnut chart)
    const userTypesCtx = document.getElementById('userTypesChart');
    if (userTypesCtx) {
        new Chart(userTypesCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Admin', 'Staff', 'Applicants', 'Guests'],
                datasets: [{
                    data: [15, 30, 45, 10],
                    backgroundColor: [
                        '#3498db',
                        '#2ecc71',
                        '#f39c12',
                        '#e74c3c'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 2000,
                    easing: 'easeOutBounce'
                }
            }
        });
    }
}

/**
 * Initialize event listeners for dashboard
 */
function initEventListeners() {
    // Add hover animations to menu cards
    const menuCards = document.querySelectorAll('.menu-card');
    menuCards.forEach(card => {
        card.addEventListener('mouseover', function() {
            this.classList.add('shadow-lg');
        });
        
        card.addEventListener('mouseout', function() {
            this.classList.remove('shadow-lg');
        });
    });

    // Feature cards hover effects
    const featureCards = document.querySelectorAll('.feature-card');
    featureCards.forEach(card => {
        card.addEventListener('click', function() {
            const link = this.getAttribute('data-link');
            if (link) {
                window.location.href = link;
            }
        });
    });
}

/**
 * Initialize task list functionality
 */
function initTaskList() {
    const taskForm = document.getElementById('taskForm');
    const taskList = document.getElementById('taskList');
    
    if (taskForm && taskList) {
        // Add task
        taskForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const taskInput = document.getElementById('newTask');
            if (taskInput.value.trim() !== '') {
                addNewTask(taskInput.value);
                taskInput.value = '';
            }
        });

        // Load initial tasks from localStorage or server
        loadTasks();
    }
}

/**
 * Add a new task to the list
 */
function addNewTask(taskText) {
    const taskList = document.getElementById('taskList');
    const taskId = 'task_' + new Date().getTime();
    
    const taskItem = document.createElement('li');
    taskItem.className = 'task-item';
    taskItem.id = taskId;
    
    const now = new Date();
    const formattedDate = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    
    taskItem.innerHTML = `
        <div class="task-checkbox">
            <input type="checkbox" id="check_${taskId}" />
            <label for="check_${taskId}"></label>
        </div>
        <div class="task-content">
            <p class="task-title">${taskText}</p>
            <div class="task-meta">Added on ${formattedDate}</div>
        </div>
        <div class="task-actions">
            <button type="button" class="edit-btn" data-id="${taskId}">
                <i class="fas fa-pencil-alt"></i>
            </button>
            <button type="button" class="delete-btn" data-id="${taskId}">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    taskList.appendChild(taskItem);
    
    // Set up event listeners for the new task
    const checkbox = document.getElementById(`check_${taskId}`);
    checkbox.addEventListener('change', function() {
        taskItem.classList.toggle('completed');
        saveTasks();
    });
    
    taskItem.querySelector('.delete-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this task?')) {
            taskItem.remove();
            saveTasks();
        }
    });
    
    taskItem.querySelector('.edit-btn').addEventListener('click', function() {
        const taskTitle = taskItem.querySelector('.task-title');
        const newText = prompt('Edit task:', taskTitle.textContent);
        if (newText !== null && newText.trim() !== '') {
            taskTitle.textContent = newText;
            saveTasks();
        }
    });
    
    saveTasks();
}

/**
 * Save tasks to localStorage
 */
function saveTasks() {
    const taskList = document.getElementById('taskList');
    const tasks = [];
    
    taskList.querySelectorAll('.task-item').forEach(taskItem => {
        const taskText = taskItem.querySelector('.task-title').textContent;
        const isCompleted = taskItem.classList.contains('completed');
        const taskId = taskItem.id;
        
        tasks.push({
            id: taskId,
            text: taskText,
            completed: isCompleted,
            date: taskItem.querySelector('.task-meta').textContent.replace('Added on ', '')
        });
    });
    
    localStorage.setItem('adminTasks', JSON.stringify(tasks));
}

/**
 * Load tasks from localStorage
 */
function loadTasks() {
    const taskList = document.getElementById('taskList');
    const savedTasks = localStorage.getItem('adminTasks');
    
    if (savedTasks) {
        const tasks = JSON.parse(savedTasks);
        
        tasks.forEach(task => {
            const taskItem = document.createElement('li');
            taskItem.className = 'task-item';
            if (task.completed) {
                taskItem.classList.add('completed');
            }
            taskItem.id = task.id;
            
            taskItem.innerHTML = `
                <div class="task-checkbox">
                    <input type="checkbox" id="check_${task.id}" ${task.completed ? 'checked' : ''} />
                    <label for="check_${task.id}"></label>
                </div>
                <div class="task-content">
                    <p class="task-title">${task.text}</p>
                    <div class="task-meta">Added on ${task.date}</div>
                </div>
                <div class="task-actions">
                    <button type="button" class="edit-btn" data-id="${task.id}">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button type="button" class="delete-btn" data-id="${task.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            
            taskList.appendChild(taskItem);
            
            // Set up event listeners for the loaded task
            const checkbox = document.getElementById(`check_${task.id}`);
            checkbox.addEventListener('change', function() {
                taskItem.classList.toggle('completed');
                saveTasks();
            });
            
            taskItem.querySelector('.delete-btn').addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this task?')) {
                    taskItem.remove();
                    saveTasks();
                }
            });
            
            taskItem.querySelector('.edit-btn').addEventListener('click', function() {
                const taskTitle = taskItem.querySelector('.task-title');
                const newText = prompt('Edit task:', taskTitle.textContent);
                if (newText !== null && newText.trim() !== '') {
                    taskTitle.textContent = newText;
                    saveTasks();
                }
            });
        });
    }
}

/**
 * Initialize file upload functionality
 */
function initFileUpload() {
    const fileUploadForm = document.getElementById('fileUploadForm');
    const uploadFeedback = document.getElementById('uploadFeedback');
    
    if (fileUploadForm && uploadFeedback) {
        fileUploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(fileUploadForm);
            uploadFeedback.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-spinner fa-spin me-2"></i>
                    Uploading file...
                </div>
            `;
            
            fetch('file_upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    uploadFeedback.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            ${data.message}
                        </div>
                    `;
                    fileUploadForm.reset();
                    
                    // Refresh file list if available
                    if (typeof refreshFileList === 'function') {
                        refreshFileList();
                    }
                } else {
                    uploadFeedback.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                uploadFeedback.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        An error occurred: ${error.message}
                    </div>
                `;
            });
        });
    }
}

/**
 * Initialize calendar functionality
 */
function initCalendar() {
    const calendarContainer = document.getElementById('adminCalendar');
    
    if (calendarContainer) {
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth();
        const currentYear = currentDate.getFullYear();
        
        renderCalendar(calendarContainer, currentMonth, currentYear);
    }
}

/**
 * Render calendar for a specific month and year
 */
function renderCalendar(container, month, year) {
    // Clear previous content
    container.innerHTML = '';
    
    // Create month header
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                        'July', 'August', 'September', 'October', 'November', 'December'];
    
    const calendarHeader = document.createElement('div');
    calendarHeader.className = 'd-flex justify-content-between align-items-center mb-3';
    calendarHeader.innerHTML = `
        <button class="btn btn-sm btn-outline-secondary prev-month">
            <i class="fas fa-chevron-left"></i>
        </button>
        <h5 class="m-0">${monthNames[month]} ${year}</h5>
        <button class="btn btn-sm btn-outline-secondary next-month">
            <i class="fas fa-chevron-right"></i>
        </button>
    `;
    container.appendChild(calendarHeader);
    
    // Add event listeners for navigation
    calendarHeader.querySelector('.prev-month').addEventListener('click', function() {
        let newMonth = month - 1;
        let newYear = year;
        if (newMonth < 0) {
            newMonth = 11;
            newYear--;
        }
        renderCalendar(container, newMonth, newYear);
    });
    
    calendarHeader.querySelector('.next-month').addEventListener('click', function() {
        let newMonth = month + 1;
        let newYear = year;
        if (newMonth > 11) {
            newMonth = 0;
            newYear++;
        }
        renderCalendar(container, newMonth, newYear);
    });
    
    // Create days header
    const daysHeader = document.createElement('div');
    daysHeader.className = 'row text-center mb-2';
    
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    dayNames.forEach(day => {
        daysHeader.innerHTML += `
            <div class="col">
                <small class="text-muted">${day}</small>
            </div>
        `;
    });
    container.appendChild(daysHeader);
    
    // Get the first day of the month
    const firstDay = new Date(year, month, 1);
    const startingDay = firstDay.getDay();
    
    // Get the last day of the month
    const lastDay = new Date(year, month + 1, 0);
    const totalDays = lastDay.getDate();
    
    // Create calendar grid
    let date = 1;
    for (let i = 0; i < 6; i++) {
        // Create a week row
        const weekRow = document.createElement('div');
        weekRow.className = 'row mb-2';
        
        for (let j = 0; j < 7; j++) {
            const dayCell = document.createElement('div');
            dayCell.className = 'col';
            
            // Only show dates for current month
            if ((i === 0 && j < startingDay) || date > totalDays) {
                dayCell.innerHTML = '<div class="calendar-day"></div>';
            } else {
                const isToday = date === new Date().getDate() && 
                               month === new Date().getMonth() && 
                               year === new Date().getFullYear();
                
                // Randomly add event indicators for demo purposes
                const hasEvent = Math.random() > 0.8;
                
                dayCell.innerHTML = `
                    <div class="calendar-day text-center ${isToday ? 'active' : ''} ${hasEvent ? 'has-event' : ''}">
                        ${date}
                    </div>
                `;
                
                date++;
            }
            weekRow.appendChild(dayCell);
        }
        
        container.appendChild(weekRow);
        
        // Stop rendering if we've displayed all days
        if (date > totalDays) {
            break;
        }
    }
}

/**
 * Initialize dark mode functionality
 */
function initDarkMode() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    
    if (darkModeToggle) {
        // Check for saved preference
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        }
        
        darkModeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            
            const isDarkModeNow = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDarkModeNow);
            
            if (isDarkModeNow) {
                darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            }
        });
    }
}

/**
 * Initialize notifications functionality
 */
function initNotifications() {
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (notificationDropdown) {
        const markAllReadBtn = notificationDropdown.querySelector('.mark-all-read');
        
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Mark all notifications as read
                const notifications = notificationDropdown.querySelectorAll('.notification-item.unread');
                notifications.forEach(item => {
                    item.classList.remove('unread');
                });
                
                // Update notification count
                const notificationBadge = document.querySelector('.notification-badge');
                if (notificationBadge) {
                    notificationBadge.textContent = '0';
                    notificationBadge.style.display = 'none';
                }
            });
        }
    }
} 