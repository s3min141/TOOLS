var IS_SIGNED = false;
var CURRENT_TAB = "login";
var CURRENT_USER = "";

var get_random_string = function (length) {
    return Math.random().toString(36).substring(2, length + 2);
}

var set_html = function (target, html, append = false) {
    if (!html) {
        html = "";
    }

    if (!append) {
        $(target).html(html);
    } else {
        $(target).append(html);
    }
}

var content_alert = function (type = "primary", title = "null", message = "null") {
    $("#alert-box").empty();
    var alertHtml = `<div id="alert-element" class="alert alert-${type}" role="alert" style="opacity: 500; display: none;"><h4>${title}</h4>${message}</div>`;
    set_html("#alert-box", alertHtml, true);

    $(`#alert-element`).fadeTo(3500, 500);
}

var data_to_blob = function (dataURI = "") {
    var byteString = atob(dataURI.split(',')[1]);
    var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
    var ab = new ArrayBuffer(byteString.length);
    var ia = new Uint8Array(ab);
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }
    var blob = new Blob([ab], {type: mimeString});
    return blob;
}

var show_error = function (errorCode) {
    set_html("#content-wrapper", `<img src="/static/image/error.jpg" style="width: 100%;height: 100%;object-fit: cover;">`);
    switch (errorCode) {
        case "unauthorized":
            content_alert("danger", "Alert", "You tried to access an unauthorized page!");
            break;
        case "already-authorized":
            content_alert("danger", "Alert", "You alreay logined!");
            break;
        case "not-found":
            content_alert("danger", "Alert", "Accessing to nonexist tab!");
            break;
        default:
            content_alert("danger", "Alert", "Unknown error!");
            break;
    }
    $("#loading-icon").attr("hidden", true);
}

var show_confirm = function (body = "null", callback) {
    $("#confirm-body").text(body);

    $("#confirm-yes-button").on("click", function () {
        callback();
    });

    $("#confirmModal").modal("show");
}

function redirect(url) {
    window.location.hash = '#' + url;
    check_is_signed();
}

function generate_sidebar(pageId = "", pageTxt = "", pageIcon = "", isFirst = false) {
    var sideBarHtml = `<li page-id="${pageId}"><a href="#/${pageId}" class="filter-item">${pageTxt}<span class="octicon octicon-${pageIcon} icon-right"></span></a></li>`;;
    if (pageId == "alert") {
        sideBarHtml = `<li page-id="${pageId}"><a class="filter-item">${pageTxt}<span class="octicon octicon-${pageIcon} icon-right"></span></a></li>`;
    }

    if (isFirst) {
        sideBarHtml += "<hr>";
    }
    return sideBarHtml;
}

function render_sidebar() {
    var sideBarHtml = "";
    if (!IS_SIGNED) {
        sideBarHtml += generate_sidebar("login", "Sign In", "sign-in", true);
        sideBarHtml += generate_sidebar("alert", "Sign in for more content", "alert");
    } else {
        sideBarHtml += generate_sidebar("logout", "Logout", "circle-slash", true);
        sideBarHtml += generate_sidebar("weather", "Weather", "heart");
        sideBarHtml += generate_sidebar("todo", "To do list", "tasklist");
        sideBarHtml += generate_sidebar("memo", "Memo", "pencil");
        sideBarHtml += generate_sidebar("drive", "Drive", "file-directory");
    }

    if (CURRENT_TAB == "aboutme") {
        sideBarHtml = generate_sidebar("aboutme", "About me", "info");
    }
    set_html("#sidebar-menu", sideBarHtml);
    $(`#sidebar-menu>li[page-id="${CURRENT_TAB}"]>a`).addClass("selected");
}

function render_contents() {
    $("#alert-box").empty();
    $("#loading-icon").removeAttr("hidden");
    set_html("#modal-list", '<div class="modal fade" id="confirmModal" data-bs-backdrop="static" tabindex="-1"> <div class="modal-dialog"> <div class="modal-content"> <div class="modal-header"> <span id="confirm-head">Confirmation</span> </div><div class="modal-body"> <span id="confirm-body"></span> </div><div class="modal-footer"> <button id="confirm-no-button" type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button> <button id="confirm-yes-button" type="button" class="btn btn-primary" data-bs-dismiss="modal">Yes</button> </div></div></div></div>');

    switch (CURRENT_TAB) {
        case "alert":
            break;
        case "login":
            if (IS_SIGNED) {
                show_error("already-authorized");
                return;
            }

            set_html("#content-wrapper", `<form onsubmit="return user_login();"><div id="output-message" class="mb-2"></div><div class="input-group mb-3"><label class="input-group-text" for="username-input">Username</label><input class="form-control input-block" tabindex="1" name="username" id="username-input" type="text" placeholder="ex) user1234" required></div><div class="input-group mb-3"><label class="input-group-text" for="password-input">Password</label><input class="form-control input-block" tabindex="2" id="password-input" name="password" placeholder="Password" type="password" required></div><div class="input-group mb-3"><div class="input-group-text"><span style="margin-right:5px;">Remember username</span><input class="form-checkbox" id="remember-username" type="checkbox"></div><button class="btn btn-sm btn-primary" tabindex="3" id="signin_button "type="submit">Sign In</button></div></form>`, false);
            load_saved_usename();
            break;
        case "weather":
            if (!IS_SIGNED) {
                show_error("unauthorized");
                return;
            }

            var geoData = getGeoInformation();
            getWeather(geoData).then(function (weatherData) {
                set_html("#content-wrapper", `<div class="card-body p-4"> <div class="d-flex"> <h6 class="flex-grow-1">${weatherData.location}</h6> <h6> ${weatherData.nowtime} </h6> </div><div class="d-flex flex-column text-center mt-5 mb-4"> <h6 class="display-4 mb-0 font-weight-bold" style="color: #1C2331;"> ${weatherData.temperature}°C </h6> <span class="small" style="color: #868B94">${weatherData.weather}</span> </div><div class="d-flex align-items-center"> <div class="flex-grow-1" style="font-size: 1rem;"> <div><i class="fas fa-wind fa-fw" style="color: #868B94;"></i> <span class="ms-1"> ${weatherData.wind} km/h </span></div><div><i class="fas fa-tint fa-fw" style="color: #868B94;"></i> <span class="ms-1"> ${weatherData.humidity}% </span> </div><div><i class="fas fa-sun fa-fw" style="color: #868B94;"></i> <span class="ms-1"> ${weatherData.sunset}h </span> </div></div><div> <div class="${weatherData.icon} fa-4x"></div> </div></div></div>`);
            });
            break;
        case "memo":
            if (!IS_SIGNED) {
                show_error("unauthorized");
                return;
            }

            render_modal();
            set_html("#content-wrapper", `<div id="inner-wrapper" style="margin-bottom: 10px;"> <div id="list-field"> <ul id="memo-list" style="max-height: 600px; overflow: scroll;"> </ul> </div><div id="input-field" style="display: flex;"> <div class="input-group"> <span class="input-group-text">Search</span> <input id="search-input" type="text" class="form-control" placeholder="search content" onkeyup="search_func(event);"> </div><button id="add-button" type="button" class="btn btn-dark" style="margin-left: 10px;" data-bs-toggle="modal" data-bs-target="#createMemoModal">Add</button> </div></div>`);
            render_memos();
            break;
        case "todo":
            if (!IS_SIGNED) {
                show_error("unauthorized");
                return;
            }

            render_modal();
            set_html("#content-wrapper", `<div class="card"> <div class="card-body" style="position: relative;min-height: 150px;max-height: 600px;overflow: auto;"> <table class="table mb-0"> <thead> <tr> <th scope="col">To do</th> <th scope="col">Status</th> <th scope="col">Priority</th> <th scope="col">Actions</th> </tr></thead> <tbody id="task-list"> </tbody> </table> </div><div class="card-footer text-end"> <button class="btn btn-danger" onclick="cleanup_task();">Remove done task</button> <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createTaskModal">Add Task</button> </div></div>`);
            render_task();
            break;
        case "drive":
            set_html("#content-wrapper", '<div id="inner-wrapper" style="margin-bottom: 10px;"> <input id="input-id" name="file-data" type="file" multiple> </div>');
            render_drive();
            break;
        case "logined":
            if (!IS_SIGNED) {
                show_error("unauthorized");
                return;
            }

            set_html("#content-wrapper", "");
            content_alert("primary", ":P", "Please select tab");
            break;
        case "logout":
            if (!IS_SIGNED) {
                show_error("unauthorized");
                return;
            }

            $.post("/user/logout", function (data) {
                redirect("/login");
            });
            break;
        case "aboutme":
            $.get("/static/Introduce.md", function (data) {
                set_html("#content-wrapper", `<div id="aboutme-inner-wrapper">${marked.parse(data)}</div>`);
            })
            break;
        default:
            show_error("not-found");
            break;
    }

    $("#loading-icon").attr("hidden", true);
}

function render_modal() {
    switch (CURRENT_TAB) {
        case "memo":
            set_html("#modal-list", `<div class="modal fade" id="createMemoModal" data-bs-backdrop="static" tabindex="-1"> <div class="modal-dialog modal-lg"> <div class="modal-content"> <div class="modal-header"> <h5 class="modal-title" id="exampleModalLabel">New memo</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div><div class="modal-body"> <form> <div class="mb-3"> <label for="recipient-name" class="col-form-label">Title:</label> <input type="text" class="form-control" id="memo-title-input" required> </div><div class="mb-3"> <label for="message-text" class="col-form-label">Content:</label> <textarea class="form-control" id="memo-content-input" rows="10" required></textarea> </div><div id="pincode-input-wrapper"> <label for="pincode-input-field" class="col-form-label">Pincode (5 digit):</label> <div class="input-group mb-3" id="pincode-input-field"> <div class="input-group-text"> <input class="form-check-input mt-0" id="memo-pincode-check" type="checkbox" onchange="$('#memo-pincode-input').attr('disabled', !this.checked)"> </div><input type="text" minlength="5" maxlength="5" onkeypress="if((event.keyCode<48)||(event.keyCode>57)){event.preventDefault();}" class="form-control" id="memo-pincode-input" disabled="true" required> </div></div></form> </div><div class="modal-footer"> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> <button id="memo-submit-button" type="button" class="btn btn-primary" onclick="add_memo();">Create Memo</button> </div></div></div></div>`, true);
            $('#createMemoModal').on('hidden.bs.modal', function () {
                $("#memo-title-input").val("");
                $("#memo-content-input").val("");
                $("#memo-pincode-input").val("");
                $("#createMemoModal").attr("edit-memo", "");
                $("#memo-submit-button").text("Crete Memo");
                $("#memo-pincode-check").prop("checked", false);
                $("#memo-pincode-input").attr("disabled", true);
                $("#pincode-input-wrapper").attr("hidden", false);
            });
            break;
        case "todo":
            set_html("#modal-list", `<div class="modal fade" id="createTaskModal" data-bs-backdrop="static" tabindex="-1"> <div class="modal-dialog modal-lg"> <div class="modal-content"> <div class="modal-header"> <h5 class="modal-title" id="exampleModalLabel">New task</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div><div class="modal-body"> <form> <div class="mb-3"> <label class="col-form-label">To do:</label> <input type="text" class="form-control" id="task-content-input" required> </div><div class="mb-3"> <label f class="col-form-label">Task priority: </label> <div class="btn-group dropend"> <button task-priority="0" id="priority-dropdown-btn" type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"> Low </button> <ul class="dropdown-menu"> <li onclick="$('#priority-dropdown-btn').attr('task-priority', '0');$('#priority-dropdown-btn').text('Low ');"><a class="dropdown-item">Low</a></li><li onclick="$('#priority-dropdown-btn').attr('task-priority', '1');$('#priority-dropdown-btn').text('Medium ');"><a class="dropdown-item">Medium</a></li><li onclick="$('#priority-dropdown-btn').attr('task-priority', '2');$('#priority-dropdown-btn').text('High ');"><a class="dropdown-item">High</a></li></ul> </div></div></form> </div><div class="modal-footer"> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> <button id="memo-submit-button" type="button" class="btn btn-primary" onclick="add_task();">Create Task</button> </div></div></div></div>`, true);
            $('#createTaskModal').on('hidden.bs.modal', function () {
                $("#task-content-input").val("");
                $("#priority-dropdown-btn").text("Low ");
                $("priority-dropdown-btn").attr("task-priority", "0");
            });
            break;
    }
}

function render_drive()
{
    configList = [];
    previewList = [];

    $("#inner-wrapper").attr("hidden", true);
    $.post("/drive/list", function (data) {
        data.forEach(element => {
            var file = {};
            var blobUrl = URL.createObjectURL(data_to_blob(element.filecontent)).toString();
            
            file.type = element.filetype.split('/')[0];
            file.caption = element.filename;
            file.size = element.filesize;
            file.url = "delete TBD";
            file.downloadUrl = blobUrl;
            configList.push(file);
            previewList.push(blobUrl);
        });

        $.fn.fileinputBsVersion = '5.0.0';
        $("#input-id").fileinput({
            theme: "explorer",
            uploadUrl: "/drive/upload",
            preferIconicPreview: true,
            removeFromPreviewOnError: true,
            overwriteInitial: false,
            initialPreviewAsData: true,
            initialPreviewConfig: configList,
            initialPreview: previewList
        });

        $("#inner-wrapper").attr("hidden", false);
    });
}

function render_memos() {
    $.post("/memo/list", function (data) {
        if (data == false) {
            content_alert("warning", "Alert", "Memo list is empty");
            return;
        }

        data.forEach(element => {
            var lockedIcon = "";
            var passwdDiv = "";
            var memoTools = "";

            if (element.islocked == "1") {
                lockedIcon = '<span class="octicon octicon-lock"></span>';
                passwdDiv = `<div style="width: 200px; margin-top: 10px;"> <label for="${element.memoid}-pincode">Pincode: </label> <input type="text" id="${element.memoid}-pincode"> </div>`;

                $(`#${element.memoid}`).ready(function () {
                    $(`#${element.memoid}-pincode`).pincodeInput({
                        inputs: 5,
                        hidedigits: true,
                        complete: function (value, e, errorElement) {
                            var parameter = {
                                memoid: element.memoid,
                                pincode: value
                            }

                            $.post("/memo/unlock", parameter, function (data) {
                                if (data != false) {
                                    set_html(`#${element.memoid}>div>div[class="card-body"]`, `<pre class="card-text">${marked.parse(data.memocontent)}</pre> <pre id="original-text" hidden>${data.memocontent}</pre>`, false);
                                    set_html(`#${element.memoid}-tools`, `<span memo-id="${element.memoid}" class="octicon octicon-trashcan" style="cursor: pointer;" onclick="remove_memo(event);"></span> <span class="octicon octicon-file-text" style="cursor: pointer;" onclick="edit_memo(event);"></span>`, false);
                                    $(`#${element.memoid}>div>h5>span`).remove();
                                    $("#alert-box").empty();
                                    search_func();
                                } else {
                                    content_alert("danger", "Alert", "Failed to unlock memo (check password T.T)");
                                }
                            });
                        }
                    });
                });
            } else {
                memoTools = `<span memo-id="${element.memoid}" class="octicon octicon-trashcan" style="cursor: pointer;" onclick="remove_memo(event);"></span> <span class="octicon octicon-file-text" style="cursor: pointer;" onclick="edit_memo(event);"></span>`;
            }

            set_html("#memo-list", `<li id="${element.memoid}" style="list-style-type: none;margin-bottom: 10px;"> <div class="card"> <h5 class="card-header">${element.memotitle} ${lockedIcon} <div id="${element.memoid}-tools" class="right" style="margin-left: 10px;">${memoTools}</div> <p class="right">${element.createtime}</p> </h5> <div class="card-body"> <pre class="card-text">${marked.parse(element.memocontent)}</pre> <pre id="original-text" hidden>${data.memocontent}</pre> ${passwdDiv} </div></div></li>`, true);
        });
    });
}

function render_task() {
    $.post("/task/list", function (data) {
        if (data != false) {
            data.forEach(element => {
                var priorityClass = "";
                var statusClass = "";
                var toolClass = "";
                var toolAction = "";

                switch (element.taskstatus) {
                    case "Done":
                        statusClass = "success";
                        toolClass = "times text-danger";
                        toolAction = "change_task_status(event, 0);";
                        break;
                    default:
                        statusClass = "secondary";
                        toolClass = "check text-success";
                        toolAction = "change_task_status(event, 1);";
                        break;
                }

                switch (element.taskpriority) {
                    case "Medium":
                        priorityClass = "warning";
                        break;
                    case "High":
                        priorityClass = "danger";
                        break;
                    default:
                        priorityClass = "success";
                        break;
                }

                set_html("#task-list", `<tr task-id="${element.taskid}" id="${element.taskid}-task"> <td> <span>${element.taskcontent}</span> </td><td class="align-middle"> <h6 class="mb-0"><span class="badge bg-${statusClass}">${element.taskstatus}</span></h6> </td><td class="align-middle"> <h6 class="mb-0"><span class="badge bg-${priorityClass}">${element.taskpriority}</span></h6> </td><td class="align-middle"> <a task-id="${element.taskid}" style="cursor: pointer;" onclick="${toolAction}"><i class="fas fa-${toolClass} me-3"></i></a> <a task-id="${element.taskid}" style="cursor: pointer;" onclick="delete_task(event);"><i class="fas fa-trash-alt text-danger"></i></a> </td></tr>`, true);
            });
        } else {
            content_alert("warning", "Alert", "Task list is empty");
        }
    });
}

function user_login() {
    event.preventDefault();

    var username = $("#username-input").val();
    var password = $("#password-input").val();

    if (username !== "" && password != "") {
        var parameter = {
            username: username,
            password: password
        };

        $.post("/user/login", parameter, function (data) {
            if (data == true) {
                if ($("#remember-username").prop("checked") == true) {
                    localStorage.setItem("saved-username", username);
                }
                redirect("/logined");
            } else {
                content_alert("danger", "Alert", "Failed to login");
            }
        });
    } else {
        content_alert("danger", "Alert", "Invalid Creditionals");
    }
}

function add_memo() {
    var parameter = {
        memoid: $("#createMemoModal").attr("edit-memo"),
        memotitle: $("#memo-title-input").val().trim(),
        memocontent: $("#memo-content-input").val().trim(),
        pincode: $("#memo-pincode-input").val(),
        islocked: $("#memo-pincode-check").is(":checked")
    };

    $.post("/memo/add", parameter, function (data) {
        $("button[class='btn-close']").click();
        if (data != false) {
            redirect("/memo");
        } else {
            content_alert("danger", "Alert", "Failed to create/edit memo");
        }
    });
}

function remove_memo(event) {
    show_confirm("Are you sure to delete this memo?", function () {
        var parameter = {
            memoid: event.target.getAttribute("memo-id")
        };

        $.post("/memo/delete", parameter, function (data) {
            if (data != false) {
                redirect("/memo");
            } else {
                content_alert("danger", "Alert", "Failed to delete memo");
            }
        });
    });
}

function edit_memo(event) {
    var memoId = event.target.parentElement.id.substr(0, 10);

    $("#memo-title-input").val($(`#${memoId}>div>h5`).clone().children().remove().end().text().trim());
    $("#memo-content-input").val($(`#${memoId}>div>div>pre`).eq(1).text().trim());

    $("#pincode-input-wrapper").attr("hidden", true);
    $("#createMemoModal").attr("edit-memo", memoId);
    $("#memo-submit-button").text("Edit Memo");
    $("#createMemoModal").modal("show");
}

function change_task_status(event, status) {
    var taskid = "";
    taskid = (event.target.tagName == "A") ? event.target.getAttribute("task-id") : event.target.parentElement.getAttribute("task-id");

    var parameter = {
        taskid: taskid,
        taskstatus: status
    };

    $.post("/task/status", parameter, function (data) {
        if (data != false) {
            redirect("/todo");
        } else {
            content_alert("danger", "Alert", "Failed to change task status");
        }
    });
}

function delete_task(event) {
    show_confirm("Are you sure to delete this task?", function () {
        var taskid = "";
        taskid = (event.target.tagName == "A") ? event.target.getAttribute("task-id") : event.target.parentElement.getAttribute("task-id");

        var parameter = {
            taskid: taskid
        };

        $.post("/task/delete", parameter, function (data) {
            if (data != false) {
                redirect("/todo");
            } else {
                content_alert("danger", "Alert", "Failed to delete task");
            }
        });
    });
}

function cleanup_task() {
    show_confirm("Are you sure to clean up task?", function () {
        $.post("/task/cleanup", function (data) {
            if (data != false) {
                redirect("/todo");
            } else {
                content_alert("danger", "Alert", "Failed to clean up task");
            }
        });
    });
}

function add_task() {
    var parameter = {
        taskcontent: $("#task-content-input").val(),
        taskpriority: $("#priority-dropdown-btn").attr("task-priority")
    };

    $.post("/task/add", parameter, function (data) {
        $("button[class='btn-close']").click();
        if (data != false) {
            redirect("/todo");
        } else {
            content_alert("danger", "Alert", "Failed to add task");
        }
    });
}

function search_func() {
    var keyWord = $("#search-input").val().toUpperCase();
    var memos = $("#memo-list").children();

    for (var i = 0; i < memos.length; i++) {
        var memoId = memos[i].id;
        var memoTitle = $(`#${memoId}>div>h5`).text().toUpperCase();
        var memoContent = $(`#${memoId}>div>div>pre`).text().toUpperCase();

        if (!memoTitle.includes(keyWord) && !memoContent.includes(keyWord)) {
            if ($(`#${memoId}>div>h5>span`).length <= 0) {
                $(`#${memoId}`).attr("hidden", "");
            }
        } else {
            $(`#${memoId}`).removeAttr("hidden");
        }
    }
}

function check_nowtab() {
    var splitedUrl = window.location.href.split("#");

    if (splitedUrl.length >= 2) {
        CURRENT_TAB = splitedUrl[1].replace("/", "");
    }
}

function load_saved_usename() {
    var username = localStorage.getItem("saved-username");

    if (username == "") {
        return;
    }
    $("#username-input").val(username);
    $("#remember-username").prop("checked", true);
}

function check_is_signed() {
    $.post("/status/profile", function (data) {
        if (data != false) {
            IS_SIGNED = true;
            CURRENT_USER = data.username;
            CURRENT_TAB = "logined";
        } else {
            IS_SIGNED = false;
        }

        load_layout();
    });
}

function load_layout() {
    check_nowtab();
    render_contents();
    render_sidebar();
}

function main() {
    if (window.location.hostname == "semineya.com") {
        CURRENT_TAB = "aboutme";
    }
    check_is_signed();
}

$(document).ready(main);
$(window).on("hashchange", function () {
    load_layout();
});