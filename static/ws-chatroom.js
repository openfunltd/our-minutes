(function() {
  WSChatRoomConnection = function(url, room_id, profile) {
    this.url = url;
    this.room_id = room_id;
    this.profile = profile;

    this.ws = new WebSocket(url);
    this.ws.onopen = this.onOpen.bind(this);
    this.ws.onmessage = this.onMessage.bind(this);
    this.ws.onclose = this.onClose.bind(this);
  };
  WSChatRoomConnection.prototype = {
    sendMessage: function(message) {
        this.ws.send(JSON.stringify({
            type: 'say',
            message: message,
        }));    
    },
    changeProfile: function(profile) {
        this.ws.send(JSON.stringify({
            type: 'set',
            profile: profile,
        }));
    },
    onMessage: function(event) {
        let data = JSON.parse(event.data);
        if (data[0] == 'room-info') {
            this.room_data = data[1].room;
            this.profiles = data[1].profiles;
            this.user_id = data[1].user_id;
            this.fire('room-info', data[1]);
        }
        if (data[0] == 'join') {
            user_id = data[1].user_id;
            profile = data[1].profile;
            this.profiles[user_id] = profile;
            this.room_data.users[user_id] = new Date().getTime();
            this.fire('join', data[1]);
        }
        if (data[0] == 'leave') {
            user_id = data[1].user_id;
            delete this.profiles[user_id];
            this.room_data.users = this.room_data.users.filter(function(item) {
                return item !== user_id;
            });
            this.fire('leave', data[1]);
        }
        if (data[0] == 'message') {
            from = data[1].from;
            name = data[1].name;
            message = data[1].message;
            time = data[1].time;
            type = data[1].type;

            this.room_data.messages.push({
                from: from,
                name: name,
                message: message,
                time: time,
                type: type,
            });
            this.fire('message', data[1]);
        }
    },
    onOpen: function(event) {
        this.ws.send(JSON.stringify({
            type: 'join',
            profile: this.profile,
            room: this.room_id,
        }));
    },
    onClose: function(event) {
    },
    on: function(event, callback) {
        this[event] = callback;
    },
    fire: function(event, data) {
        this[event](data);
    },
  };

  const WSChatRoom = {
    init: function(options) {
    },
    connect: function(url, room_id, profile) {
      return new WSChatRoomConnection(url, room_id, profile);
    },
  };

  WSChatRoom.init({});

  window.WSChatRoom = WSChatRoom;
})();


