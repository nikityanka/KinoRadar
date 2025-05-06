function delComment(commentid) {
    var confirmation = confirm("Вы действительно хотите удалить свой отзыв?");

    if (confirmation) {
        $.ajax({
            url: 'php/delComment',
            type: 'post',
            data: {
                id: commentid,
            },
            success: function (data) {
                window.location.reload();
            },
            error: function () {
                alert("Ошибка! Попробуйте позже.");
            }
        });
    }
}

function favorite(idmovie, button_img) {
    return $.ajax({
        url: 'php/favorite',
        type: 'post',
        data: { id: idmovie },
        dataType: 'json',
        success: function (response) {
            if (response.type === 'added') {
                button_img.src = "media/heart-fill.svg";
            }
            else if (response.type === 'removed') {
                button_img.src = "media/heart.svg";
            }

            console.log(response.text);
        },
        error: function (xhr) {
            alert("Ошибка: " + xhr.responseJSON?.text || 'Неизвестная ошибка');
        }
    });
}

function editComment(idComment, description) {
    return $.ajax({
        url: 'php/editComment',
        type: 'post',
        data: {
            id: idComment,
            comment: description
        },
        success: function (result) {
        },
        error: function (result) {
            alert("Ошибка!" + result["text"]);
        }
    });
}

// Мягкое удаление
function scheduleDelete(movieId, row) {
    return $.ajax({
        url: '../php/scheduleDelete',
        type: 'post',
        data: {
            id: movieId
        },
        success: function(result) {
            const data = JSON.parse(result);
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Ошибка: ' + data.message);
            }
        },
        error: function() {
            alert('Ошибка сети');
        }
    });
}

// Отмена удаления
function cancelDelete(movieId, row) {
    return $.ajax({
        url: '../php/cancelDelete',
        type: 'post',
        data: {
            id: movieId
        },
        success: function(result) {
            const data = JSON.parse(result);
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Ошибка: ' + data.message);
            }
        },
        error: function() {
            alert('Ошибка сети');
        }
    });
}

// Принудительное удаление
function delMovie(movieid) {
    return $.ajax({
        url: '../php/delMovie',
        type: 'post',
        data: {
            id: movieid
        },
        success: function (result) {
            const element = document.querySelector(`[data-id="${movieid}"]`);
            if (element) {
                element.remove();
            }
            console.log(JSON.parse(result));
        },
        error: function (result) {
            alert("Ошибка!" + result["text"]);
        }
    });
}


function delUserInfo(userid, row) {
    return $.ajax({
        url: '../php/clearUserAbout',
        type: 'post',
        data: {
            id: userid
        },
        success: function (result) {
            const img = row.querySelector('img.mark');
            const button = row.querySelector('.enabled');
            button.classList.remove('enabled');
            button.style.opacity = '.3';
            img.src = '../media/krestik.svg';
        },
        error: function (result) {
            alert("Ошибка!" + result);
        }
    });
}


function delCommentAdmin(commentid, adminid) {
    return $.ajax({
        url: '../php/delCommentAdmin',
        type: 'post',
        data: {
            commentid: commentid,
            adminid: adminid
        },
        success: function (result) {
            const element = document.querySelector(`[data-id="${commentid}"]`);
            if (element) {
                element.remove();
            }
        },
        error: function (result) {
            alert("Ошибка!" + result);
        }
    });
}

function addData(value, tableId) {
    let dbTableName;

    switch (tableId) {
        case 'countries':
            dbTableName = 'country';
            break;
        case 'directors':
            dbTableName = 'director';
            break;
        case 'genres':
            dbTableName = 'genre'; 
            break;
        default:
            console.error('Unknown table type');
            return;
    }

    return $.ajax({
        url: '../php/addData',
        type: 'POST',
        data: {
            value: value,
            table: dbTableName
        },
        success: function (result) {
            window.location.reload();
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            alert('Произошла ошибка при добавлении данных');
        }
    });
}

function delData(value, tableId) {
    let dbTableName;

    switch (tableId) {
        case 'countries':
            dbTableName = 'country';
            break;
        case 'directors':
            dbTableName = 'director';
            break;
        case 'genres':
            dbTableName = 'genre'; 
            break;
        default:
            console.error('Unknown table type');
            return;
    }

    return $.ajax({
        url: '../php/delData',
        type: 'POST',
        data: {
            value: value,
            table: dbTableName
        },
        success: function (result) {
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            alert('Произошла ошибка при добавлении данных');
        }
    });
}