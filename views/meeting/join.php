<?= $this->partial('common/header') ?>
<h1>歡迎使用審議小幫手</h1>
<form method="post" id="join-form">
    <input type="hidden" name="csrf_token" value="<?= $this->escape($this->csrf_token) ?>">
    <div class="mb-3">
        會議：<?= $this->escape($this->meeting->d('name')) ?>
    </div>
    <div class="alert alert-light" role="alert"><?= $this->escape($this->meeting->d('intro')) ?></div>
    <div class="mb-3">
        <label for="exampleFormControlInput1" class="form-label">該如何稱呼您？</label>
        <input type="text" name="name" class="form-control" id="exampleFormControlInput1" value="">
    </div>
    <div class="mb-3">
        <label for="exampleFormControlTextarea1" class="form-label">您的簡介（可不填寫）</label>
        <textarea class="form-control" name="intro" id="exampleFormControlTextarea1" rows="3"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">加入會議</button>
</form>
<div id="area-dashboard" style="display:none">
    <p>會議：<?= $this->escape($this->meeting->d('name')) ?></p>
    <p>議程：<span id="current-agenda"></span></p>
    <button type="button" class="btn btn-primary" id="action-raise-hand">舉手
        <span id="raise-hand-status"></span>
    </button>
    <button type="button" class="btn btn-primary">我要發言</button>

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
            <textarea class="form-control" id="exampleFormControlTextarea1" rows="8">我覺得政府應該請各行業優先盤點會受到 AI 衝擊的行業 請各行業自行評估受到衝擊 預計減少的人力數量 然後政府根據統計公布行業排名 並且公布提早建議求職者轉職或挑選其他工作 至於現在還在該職場的 希望政府要鼓勵老闆要投入資源讓該員工學會 AI 工具 讓現有員工都成為好的 AI 操作員 這樣他們並不是被 AI 取代 而是與 AI 協作 也能推進產業發展</textarea>
            <p>您可以手動修改 AI 辨識錯誤的地方</p>
            <button type="button" class="btn btn-primary">確認無誤，嘗試用 AI 摘要</button>
            <h1>AI 摘要</h1>
            <textarea class="form-control" rows="5">1. 政府應該請各行業優先盤點會受到 AI 衝擊的行業
2. 請各行業自行評估受到衝擊，預計減少的人力數量
3. 政府根據統計公布行業排名
4. 公布提早建議求職者轉職或挑選其他工作
5. 希望政府要鼓勵老闆要投入資源讓該員工學會 AI 工具</textarea>
    <button type="button" class="btn btn-primary">確認無誤，發言</button>
        </div>
        <div class="tab-pane fade" id="alltalk" role="tabpanel" aria-labelledby="alltalk-tab">

    </div>
</div>
<script>
var webSocket = null;
var websocket_url = <?= json_encode(getenv('WEBSOCKET_URL')) ?>;
var room_id = <?= json_encode('ourminutes-' . $this->meeting->uid) ?>;

$('#join-form').submit(function(e){
  e.preventDefault();
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
        console.log(data);
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
            $('.card-header', card_dom).text(profile.name);
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
</script>
<?= $this->partial('common/footer') ?>
