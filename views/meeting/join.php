<?= $this->partial('common/header') ?>
<h1>歡迎使用審議小幫手</h1>
<form method="post" id="join-form">
    <input type="hidden" name="csrf_token" value="<?= $this->escape($this->csrf_token) ?>">
    <div class="mb-3">
        會議：<?= $this->escape($this->meeting->d('name')) ?>
    </div>
    <div class="alert alert-light" role="alert"><?= $this->escape($this->meeting->d('intro')) ?></div>
    <div class="mb-3">
        <label for="my-name" class="form-label">該如何稱呼您？</label>
        <input type="text" name="name" class="form-control" id="my-name" value="">
    </div>
    <div class="mb-3">
        <label for="my-intro" class="form-label">您的簡介（可不填寫）</label>
        <textarea class="form-control" name="intro" id="my-intro" rows="3"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">加入會議</button>
</form>
<div id="area-dashboard" style="display:none">
    <p>會議：<?= $this->escape($this->meeting->d('name')) ?></p>
    <p>議程：<span id="current-agenda"></span></p>
    <button type="button" class="btn btn-primary" id="action-raise-hand">舉手
        <span id="raise-hand-status"></span>
    </button>
    <button type="button" class="btn btn-primary" id="action-speak">我要發言
        <span id="speaking-status"></span>
    </button>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="home-tab" data-bs-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">會議</a>
        <li class="nav-item" role="presentation">
            <!-- 成員 -->
            <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">成員(<span id="person-count"></span>)</a>
        </li>
        <li class="nav-item" role="presentation">
            <!-- 議程 -->
            <a class="nav-link" id="mytalk-tab" data-bs-toggle="tab" href="#mytalk" role="tab" aria-controls="mytalk" aria-selected="false">我的發言</a>
        </li>
        <li class="nav-item" role="presentation">
            <!-- 所有人發言 -->
            <a class="nav-link" id="alltalk-tab" data-bs-toggle="tab" href="#alltalk" role="tab" aria-controls="alltalk" aria-selected="false">所有人發言</a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
            <p>會議：<?= $this->escape($this->meeting->d('name')) ?></p>
            <div class="alert alert-light" role="alert"><?= $this->escape($this->meeting->d('intro')) ?></div>
            <p>會議時間：<?= $this->escape($this->meeting->d('time')) ?></p>
            <p>會議議程：</p>
            <ul>
                <?php foreach ($this->meeting->getAgendas() as $agenda) { ?>
                <li><?= $this->escape($agenda) ?></li>
                <?php } ?>
            </ul>
        </div>
        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
        </div>
<script id="tmpl-person-card" type="text/html">
<div class="card">
    <h5 class="card-header"><span class="name"></span><span class="is_raised"></span></h5>
    <div class="card-body">
        <h5 class="card-title">很會討論有限公司專案經理</h5>
    </div>
</div>
</script>
        <div class="tab-pane fade" id="mytalk" role="tabpanel" aria-labelledby="mytalk-tab">
            <h1>您的發言</h1>
            <textarea class="form-control" id="myspeak" rows="8"></textarea>
            <p>您可以手動修改 AI 辨識錯誤的地方</p>
            <button type="button" class="btn btn-primary">確認無誤，嘗試用 AI 摘要</button>
            <h1>AI 摘要</h1>
            <textarea class="form-control" rows="5"></textarea>
            <button type="button" class="btn btn-primary">確認無誤，發言</button>
        </div>
        <div class="tab-pane fade" id="alltalk" role="tabpanel" aria-labelledby="alltalk-tab">

    </div>
</div>
<script>
var webSocket = null;
var websocket_url = <?= json_encode(getenv('WEBSOCKET_URL')) ?>;
var room_id = <?= json_encode('ourminutes-' . $this->meeting->uid) ?>;

if (localStorage.getItem('name')) {
    $('#join-form input[name="name"]').val(localStorage.getItem('name'));
}
if (localStorage.getItem('intro')) {
    $('#join-form textarea[name="intro"]').val(localStorage.getItem('intro'));
}
$('#join-form').submit(function(e){
    e.preventDefault();
    localStorage.setItem('name', $('#join-form input[name="name"]').val());
    localStorage.setItem('intro', $('#join-form textarea[name="intro"]').val());
  if (!webSocket) {
    webSocket = new WebSocket(websocket_url);
    webSocket.onopen = function() {
        webSocket.send(JSON.stringify({
            type: 'join',
            profile: {
                name: $('#join-form input[name="name"]').val(),
                intro: $('#join-form textarea[name="intro"]').val(),
            },
            room: room_id,
        }));
    };
    webSocket.onmessage = function(event) {
        var data = JSON.parse(event.data);
        if (data[0] == 'room-info') {
            $('#profile').html('');
            room_data = data[1].room;
            profiles = data[1].profiles;
            for (var user_id in room_data.users) {
                profile = profiles[user_id];
                var card_dom = $($('#tmpl-person-card').html());
				card_dom.attr('data-user-id', user_id);
                $('.name', card_dom).text(profile.name);
                $('.card-title', card_dom).text(profile.intro);
                $('#profile').append(card_dom);
            }
            $('#person-count').text($('#profile .card').length);
        } else if (data[0] == 'join') {
			user_id = data[1].user_id;
            profile = data[1].profile;
            if ($('.card[data-user-id="' + user_id + '"]').length) {
                return;
            }
            var card_dom = $($('#tmpl-person-card').html());
            card_dom.attr('data-user-id', user_id);
            $('.name', card_dom).text(profile.name);
            $('.card-title', card_dom).text(profile.intro);
            $('#profile').append(card_dom);
            $('#person-count').text($('#profile .card').length);
        } else if (data[0] == 'set') {
            user_id = data[1].user_id;
            profile = data[1].profile;
            if (profile.raise_hand) {
                $('.card[data-user-id="' + user_id + '"] .is_raised').text('🙋');
            } else {
                $('.card[data-user-id="' + user_id + '"] .is_raised').text('');
            }
        } else if (data[0] == 'leave') {
            user_id = data[1].user_id;
            $('.card[data-user-id="' + user_id + '"]').remove();
            $('#person-count').text($('#profile .card').length);
        }
    };
    webSocket.onclose = function() {
    };
  }
  $('#join-form').hide();
  $('#area-dashboard').show();
});

$('#action-raise-hand').click(function(){
    if ($('#action-raise-hand').is('.is_raised')) {
        webSocket.send(JSON.stringify({
            type: 'set',
            profile: {
                raise_hand: null,
            }
        }));
        $('#action-raise-hand').removeClass('is_raised');
        $('#raise-hand-status').text('');
        return;
    } else {
        webSocket.send(JSON.stringify({
            type: 'set',
            profile: {
                raise_hand: new Date().getTime(),
            }
        }));
        $('#action-raise-hand').addClass('is_raised');
        // emoji hand
        $('#raise-hand-status').text('🙋');
    
    }
});

let getHHiiss = function(date) {
    // 00:00:00
    t = [date.getHours(), date.getMinutes(), date.getSeconds()];
    for (i = 0; i < t.length; i++) {
        if (t[i] < 10) {
            t[i] = '0' + t[i];
        }
    }
    return t.join(':');
};

let build_speaking_message = function() {
    let message = '';
    for (let i = 0; i < speaking_log.sentences.length; i++) {
        // HH:ii:ss
        start_time = new Date(speaking_log.sentences[i].start);
        end_time = new Date(speaking_log.sentences[i].end);
        message += '[' + getHHiiss(start_time) + ' - ' + getHHiiss(end_time) + '] ';
        message += speaking_log.sentences[i].text;
        message += "\n";
    }
    if (speaking_log.draft_start) {
        message += '[' + getHHiiss(new Date(speaking_log.draft_start)) + ' - ] ';
        message += speaking_log.draft;
    }
    return message;
};

let recognition = null;
speaking_log = {};
$('#action-speak').click(function(e){
    e.preventDefault();
    $('#mytalk-tab').tab('show');
    if (!recognition) {
        $('#speaking-status').text('🎤');
        recognition = new webkitSpeechRecognition();
        speaking_log.start = new Date().getTime();
        speaking_log.draft_start = null;
        speaking_log.sentences = [];
        speaking_log.draft = '';

        //recognition.continuous = true;
        recognition.interimResults = true;
        //recognition.lang = 'cmn-Hant-TW';
        recognition.onresult = function(event) {
            for (var i = event.resultIndex; i < event.results.length; ++i) {
                if (null === speaking_log.draft_start) {
                    speaking_log.draft_start = new Date().getTime();
                }
                if (event.results[i].isFinal) {
                    text = event.results[i][0].transcript;
                    if (text.length) {
                        speaking_log.sentences.push({
                            start: speaking_log.draft_start,
                            end: new Date().getTime(),
                            text: event.results[i][0].transcript,
                        });
                        speaking_log.draft = '';
                    }
                    speaking_log.draft_start = null;
                } else {
                    if (event.results[i][0].transcript.length) {
                        speaking_log.draft = event.results[i][0].transcript;
                    }
                }
            }
            message = build_speaking_message();
            $('#myspeak').val(message);
            webSocket.send(JSON.stringify({
                type: 'set',
                profile: {
                    speaking: message,
                }
            }));
        };
        recognition.onend = function(event){
                recognition.start();
        };
        recognition.start();
    } else {
        $('#speaking-status').text('');
        recognition.stop();
        recognition = null;
    }
});

</script>
<?= $this->partial('common/footer') ?>
