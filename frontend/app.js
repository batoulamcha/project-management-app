/**
 * Project Management App - Main Application Logic
 * DevExtreme Frontend with PHP Backend Integration
 */

let authToken = localStorage.getItem('authToken') || null;

// Initialize App
$(document).ready(function() {
    if (authToken) {
        showMainApp();
    } else {
        showLogin();
    }
});

/**
 * Show Login Form
 */
function showLogin() {
    $('#login-container').show();
    $('#main-container').hide();
    
    $('#login-form').dxForm({
        items: [
            {
                itemType: 'simple',
                dataField: 'username',
                label: { text: 'Username' },
                editorOptions: {
                    placeholder: 'Enter username'
                },
                validationRules: [{
                    type: 'required',
                    message: 'Username is required'
                }]
            },
            {
                itemType: 'simple',
                dataField: 'password',
                label: { text: 'Password' },
                editorType: 'dxTextBox',
                editorOptions: {
                    mode: 'password',
                    placeholder: 'Enter password'
                },
                validationRules: [{
                    type: 'required',
                    message: 'Password is required'
                }]
            },
            {
                itemType: 'button',
                buttonOptions: {
                    text: 'Login',
                    type: 'success',
                    onClick: handleLogin,
                    width: '100%'
                }
            }
        ],
        formData: {
            username: '',
            password: ''
        }
    });
}

/**
 * Handle Login
 */
function handleLogin(e) {
    const form = $('#login-form').dxForm('instance');
    const validationResult = form.validate();
    
    if (!validationResult.isValid) {
        DevExpress.ui.notify('Please fill in all required fields', 'error', 3000);
        return;
    }
    
    const formData = form.option('formData');
    
    $.ajax({
        url: `${API_BASE_URL}/auth`,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response && response.success && response.data && response.data.token) {
                authToken = response.data.token;
                localStorage.setItem('authToken', authToken);
                showMainApp();
                DevExpress.ui.notify('Login successful!', 'success', 2000);
            } else {
                DevExpress.ui.notify('Invalid response from server', 'error', 3000);
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON || { message: 'Login failed' };
            DevExpress.ui.notify(error.message || 'Login failed', 'error', 3000);
        }
    });
}

/**
 * Show Main Application
 */
function showMainApp() {
    $('#login-container').hide();
    $('#main-container').show();
    
    initializeToolbar();
    initializeDataGrid();
}

/**
 * Initialize Toolbar
 */
function initializeToolbar() {
    $('#toolbar').dxToolbar({
        items: [
            {
                location: 'before',
                widget: 'dxButton',
                options: {
                    text: 'Add Task',
                    type: 'default',
                    icon: 'plus',
                    onClick: function() {
                        showTaskForm();
                    }
                }
            },
            {
                location: 'after',
                widget: 'dxButton',
                options: {
                    text: 'Logout',
                    type: 'danger',
                    icon: 'user',
                    onClick: function() {
                        logout();
                    }
                }
            },
            {
                location: 'after',
                widget: 'dxButton',
                options: {
                    text: 'Refresh',
                    type: 'normal',
                    icon: 'refresh',
                    onClick: function() {
                        refreshDataGrid();
                    }
                }
            }
        ]
    });
}

/**
 * Initialize DataGrid
 */
function initializeDataGrid() {
    $('#dataGrid').dxDataGrid({
        dataSource: {
            load: function(loadOptions) {
                return loadTasks();
            }
        },
        columns: [
            {
                dataField: 'id',
                caption: 'ID',
                width: 80,
                alignment: 'center'
            },
            {
                dataField: 'project_name',
                caption: 'Project',
                width: 200
            },
            {
                dataField: 'title',
                caption: 'Title',
                minWidth: 200
            },
            {
                dataField: 'description',
                caption: 'Description',
                minWidth: 250
            },
            {
                dataField: 'status',
                caption: 'Status',
                width: 120,
                cellTemplate: function(container, options) {
                    const status = options.value || 'pending';
                    const badgeClass = `status-badge status-${status.toLowerCase().replace(' ', '_')}`;
                    container.append(`<span class="${badgeClass}">${status}</span>`);
                }
            },
            {
                dataField: 'priority',
                caption: 'Priority',
                width: 100,
                cellTemplate: function(container, options) {
                    const priority = options.value || 'medium';
                    const badgeClass = `priority-badge priority-${priority.toLowerCase()}`;
                    container.append(`<span class="${badgeClass}">${priority}</span>`);
                }
            },
            {
                dataField: 'due_date',
                caption: 'Due Date',
                width: 120,
                dataType: 'date',
                format: 'shortDate'
            },
            {
                caption: 'Actions',
                width: 150,
                alignment: 'center',
                allowSorting: false,
                cellTemplate: function(container, options) {
                    const $editBtn = $('<div>')
                        .dxButton({
                            text: 'Edit',
                            icon: 'edit',
                            hint: 'Edit Task',
                            type: 'default',
                            stylingMode: 'outlined',
                            width: 70,
                            onClick: function() {
                                showTaskForm(options.data);
                            }
                        });
                    
                    const $deleteBtn = $('<div>')
                        .dxButton({
                            text: 'Delete',
                            icon: 'trash',
                            hint: 'Delete Task',
                            type: 'danger',
                            stylingMode: 'outlined',
                            width: 70,
                            onClick: function() {
                                deleteTask(options.data.id);
                            }
                        });
                    
                    const $container = $('<div>').css({
                        display: 'flex',
                        gap: '5px',
                        justifyContent: 'center'
                    });
                    
                    $container.append($editBtn);
                    $container.append($deleteBtn);
                    container.append($container);
                }
            }
        ],
        paging: {
            pageSize: 20
        },
        pager: {
            showPageSizeSelector: true,
            allowedPageSizes: [10, 20, 50, 100],
            showInfo: true
        },
        searchPanel: {
            visible: true,
            width: 300,
            placeholder: 'Search tasks...'
        },
        filterRow: {
            visible: true
        },
        headerFilter: {
            visible: true
        },
        columnChooser: {
            enabled: true
        },
        export: {
            enabled: true,
            fileName: 'tasks-export'
        },
        showBorders: true,
        rowAlternationEnabled: true,
        hoverStateEnabled: true,
        allowColumnReordering: true,
        allowColumnResizing: true,
        columnAutoWidth: true
    });
}

/**
 * Load Tasks from API
 */
function loadTasks() {
    return $.ajax({
        url: `${API_BASE_URL}/tasks`,
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${authToken}`,
            'X-API-Token': authToken
        }
    }).then(function(response) {
        if (response && response.success) {
            return response.data;
        }
        throw new Error(response.message || 'Failed to load tasks');
    }).fail(function(xhr, status, error) {
        throw new Error('Failed to load tasks: ' + (xhr.responseJSON?.message || error));
    });
}

/**
 * Show Task Form (Add/Edit)
 */
function showTaskForm(taskData = null) {
    const isEdit = taskData !== null;
    
    // Load projects for dropdown
    $.ajax({
        url: `${API_BASE_URL}/projects`,
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${authToken}`,
            'X-API-Token': authToken
        },
        success: function(response) {
            if (!response || !response.success) {
                DevExpress.ui.notify('Failed to load projects', 'error', 3000);
                return;
            }
            
            const projects = response.data || [];
            
            $('#task-form-popup').dxPopup({
                title: isEdit ? 'Edit Task' : 'Add New Task',
                width: 600,
                height: 'auto',
                showTitle: true,
                visible: true,
                dragEnabled: true,
                closeOnOutsideClick: true,
                contentTemplate: function() {
                    const $form = $('<div id="task-form"></div>');
                    
                    $form.dxForm({
                        items: [
                            {
                                itemType: 'simple',
                                dataField: 'project_id',
                                label: { text: 'Project' },
                                editorType: 'dxSelectBox',
                                editorOptions: {
                                    dataSource: projects,
                                    displayExpr: 'name',
                                    valueExpr: 'id',
                                    placeholder: 'Select a project'
                                },
                                validationRules: [{
                                    type: 'required',
                                    message: 'Project is required'
                                }]
                            },
                            {
                                itemType: 'simple',
                                dataField: 'title',
                                label: { text: 'Title' },
                                editorOptions: {
                                    placeholder: 'Enter task title'
                                },
                                validationRules: [{
                                    type: 'required',
                                    message: 'Title is required'
                                }]
                            },
                            {
                                itemType: 'simple',
                                dataField: 'description',
                                label: { text: 'Description' },
                                editorType: 'dxTextArea',
                                editorOptions: {
                                    placeholder: 'Enter task description',
                                    height: 100
                                }
                            },
                            {
                                itemType: 'group',
                                caption: 'Task Details',
                                items: [
                                    {
                                        itemType: 'simple',
                                        dataField: 'status',
                                        label: { text: 'Status' },
                                        editorType: 'dxSelectBox',
                                        editorOptions: {
                                            dataSource: ['pending', 'in_progress', 'completed'],
                                            placeholder: 'Select status'
                                        }
                                    },
                                    {
                                        itemType: 'simple',
                                        dataField: 'priority',
                                        label: { text: 'Priority' },
                                        editorType: 'dxSelectBox',
                                        editorOptions: {
                                            dataSource: ['low', 'medium', 'high', 'critical'],
                                            placeholder: 'Select priority'
                                        }
                                    },
                                    {
                                        itemType: 'simple',
                                        dataField: 'due_date',
                                        label: { text: 'Due Date' },
                                        editorType: 'dxDateBox',
                                        editorOptions: {
                                            type: 'date',
                                            placeholder: 'Select due date'
                                        }
                                    }
                                ]
                            },
                            {
                                itemType: 'button',
                                buttonOptions: {
                                    text: isEdit ? 'Update Task' : 'Create Task',
                                    type: 'success',
                                    onClick: function() {
                                        saveTask(isEdit, taskData ? taskData.id : null);
                                    },
                                    width: '100%'
                                }
                            }
                        ],
                        formData: taskData || {
                            project_id: null,
                            title: '',
                            description: '',
                            status: 'pending',
                            priority: 'medium',
                            due_date: null
                        }
                    });
                    
                    return $form;
                }
            });
        },
        error: function(xhr, status, error) {
            DevExpress.ui.notify('Failed to load projects: ' + (xhr.responseJSON?.message || error), 'error', 3000);
        }
    });
}

/**
 * Save Task (Create or Update)
 */
function saveTask(isEdit, taskId) {
    const form = $('#task-form').dxForm('instance');
    const validationResult = form.validate();
    
    if (!validationResult.isValid) {
        DevExpress.ui.notify('Please fill in all required fields', 'error', 3000);
        return;
    }
    
    const formData = form.option('formData');
    
    const url = isEdit 
        ? `${API_BASE_URL}/tasks/${taskId}`
        : `${API_BASE_URL}/tasks`;
    
    const method = isEdit ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        method: method,
        contentType: 'application/json',
        headers: {
            'Authorization': `Bearer ${authToken}`,
            'X-API-Token': authToken
        },
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                DevExpress.ui.notify(
                    isEdit ? 'Task updated successfully!' : 'Task created successfully!',
                    'success',
                    2000
                );
                $('#task-form-popup').dxPopup('instance').hide();
                refreshDataGrid();
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON || { message: 'Operation failed' };
            DevExpress.ui.notify(error.message || 'Operation failed', 'error', 3000);
        }
    });
}

/**
 * Delete Task
 */
function deleteTask(taskId) {
    DevExpress.ui.dialog.confirm('Are you sure you want to delete this task?', 'Confirm Delete')
        .done(function(dialogResult) {
            if (dialogResult) {
                $.ajax({
                    url: `${API_BASE_URL}/tasks/${taskId}`,
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'X-API-Token': authToken
                    },
                    success: function(response) {
                        if (response.success) {
                            DevExpress.ui.notify('Task deleted successfully!', 'success', 2000);
                            refreshDataGrid();
                        }
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON || { message: 'Delete failed' };
                        DevExpress.ui.notify(error.message || 'Delete failed', 'error', 3000);
                    }
                });
            }
        });
}

/**
 * Refresh DataGrid
 */
function refreshDataGrid() {
    const dataGrid = $('#dataGrid').dxDataGrid('instance');
    if (dataGrid) {
        dataGrid.refresh();
    }
}

/**
 * Logout
 */
function logout() {
    DevExpress.ui.dialog.confirm('Are you sure you want to logout?', 'Confirm Logout')
        .done(function(dialogResult) {
            if (dialogResult) {
                authToken = null;
                localStorage.removeItem('authToken');
                showLogin();
                DevExpress.ui.notify('Logged out successfully', 'success', 2000);
            }
        });
}
