<?php
session_start();
if (empty($_SESSION['kode_pengguna'])) {
    header('Location: ../login.php');
    exit;
}

include '../config/database.php';
$id_zoom = (int) ($_GET['id_zoom'] ?? $_GET['id_kegiatan'] ?? 0);
$level = strtolower($_SESSION['level'] ?? '');

$stmt = $kon->prepare('SELECT z.* FROM tbl_zoom z WHERE z.id_zoom = ? LIMIT 1');
$stmt->bind_param('i', $id_zoom);
$stmt->execute();
$meeting = $stmt->get_result()->fetch_assoc();

if (!$meeting) {
    http_response_code(403);
    exit('Meeting tidak tersedia untuk akun ini.');
}

$display_name = $_SESSION['nama_admin'] ?? $_SESSION['nama_siswa'] ?? $_SESSION['username'];
$meeting_title = $meeting['judul'];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($meeting_title, ENT_QUOTES, 'UTF-8'); ?> | SMART PKL</title>
    <link rel="stylesheet" href="../template/css/bootstrap.min.css">
    <link rel="stylesheet" href="../template/css/font-awesome.min.css">
    <style>
        :root { --ink:#e2e8f0; --muted:#94a3b8; --panel:#111827; --panel-soft:#1e293b; --accent:#38bdf8; }
        body { margin:0; min-height:100vh; background:#020617; color:var(--ink); font-family:Segoe UI,sans-serif; }
        .meeting-shell { display:flex; flex-direction:column; min-height:100vh; }
        .meeting-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:14px 20px; background:#0f172a; border-bottom:1px solid #334155; }
        .meeting-header h1 { margin:0; font-size:1.05rem; }
        .meeting-header small { color:var(--muted); }
        .video-grid { flex:1; display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:12px; padding:16px; align-content:center; }
        .video-tile { position:relative; min-height:210px; overflow:hidden; border-radius:10px; background:#1e293b; border:1px solid #334155; cursor:pointer; }
        .video-tile.pinned, .video-tile.screen-presenter { grid-column:1 / -1; min-height:65vh; }
        .video-tile.pinned video, .video-tile.screen-presenter video { min-height:65vh; object-fit:contain; }
        .video-tile video { display:block; width:100%; height:100%; min-height:210px; object-fit:cover; background:#0f172a; }
        .video-name { position:absolute; left:10px; bottom:8px; padding:4px 8px; border-radius:4px; background:rgba(0,0,0,.65); font-size:.8rem; }
        .meeting-controls { display:flex; justify-content:center; gap:10px; padding:14px; background:#0f172a; border-top:1px solid #334155; }
        .meeting-controls button { width:44px; height:44px; border:0; border-radius:50%; color:#fff; background:#334155; }
        .meeting-controls button.active { background:#0284c7; }
        .meeting-controls button.leave { background:#dc2626; }
        .meeting-note { padding:10px 20px; color:var(--muted); text-align:center; font-size:.8rem; }
        @media (max-width:600px) { .video-grid { grid-template-columns:1fr; } .meeting-header { padding:12px; } }
    </style>
</head>
<body>
<div class="meeting-shell">
    <header class="meeting-header">
        <div><h1><?php echo htmlspecialchars($meeting_title, ENT_QUOTES, 'UTF-8'); ?></h1><small><?php echo htmlspecialchars($meeting['tanggal'] . ' | ' . $meeting['waktu_awal'] . ' - ' . $meeting['waktu_akhir'], ENT_QUOTES, 'UTF-8'); ?></small></div>
        <strong><?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?></strong>
    </header>
    <main id="videoGrid" class="video-grid"></main>
    <div id="meetingNote" class="meeting-note">Menghubungkan kamera dan mikrofon. Gunakan jaringan stabil untuk hasil terbaik.</div>
    <nav class="meeting-controls">
        <button id="toggleMic" class="active" title="Mute mikrofon"><i class="fa fa-microphone"></i></button>
        <button id="toggleCamera" class="active" title="Matikan kamera"><i class="fa fa-video-camera"></i></button>
        <button id="toggleScreen" title="Bagikan layar"><i class="fa fa-desktop"></i></button>
        <button id="leaveMeeting" class="leave" title="Keluar meeting"><i class="fa fa-phone"></i></button>
    </nav>
</div>
<script>
(function () {
    var roomId = <?php echo (int) $id_zoom; ?>;
    var myId = <?php echo json_encode(session_id()); ?>;
    var displayName = <?php echo json_encode($display_name); ?>;
    var signalUrl = 'meeting_signal.php';
    var localStream = null;
    var peers = {};
    var pendingCandidates = {};
    var lastSignalId = 0;
    var micEnabled = true;
    var cameraEnabled = true;
    var screenStream = null;
    var sharingScreen = false;
    var cameraTrack = null;
    var remoteStreams = {};
    var presenters = {};

    function attendanceRequest(action) {
        return fetch('../apps/zoom_attendance.php', {
            method:'POST',
            body:new URLSearchParams({action:action, id_zoom:roomId}),
            credentials:'same-origin'
        }).catch(function () {});
    }

    setInterval(function () { attendanceRequest('checkin'); }, 30000);

    function addTile(id, stream, name) {
        var tile = document.getElementById('tile-' + id);
        if (!tile) {
            tile = document.createElement('div');
            tile.className = 'video-tile';
            tile.id = 'tile-' + id;
            tile.innerHTML = '<video autoplay playsinline></video><span class="video-name"></span>';
            document.getElementById('videoGrid').appendChild(tile);
        }
        var video = tile.querySelector('video');
        video.srcObject = stream;
        video.muted = id === 'local';
        video.onloadedmetadata = function () { video.play().catch(function () {}); };
        video.oncanplay = function () { video.play().catch(function () {}); };
        video.play().catch(function () {
            document.getElementById('meetingNote').textContent = 'Klik halaman meeting sekali agar suara peserta terdengar.';
        });
        tile.querySelector('.video-name').textContent = name || 'Peserta';
        tile.onclick = function () { tile.classList.toggle('pinned'); };
        if (presenters[id]) {
            tile.classList.add('screen-presenter');
            tile.querySelector('.video-name').textContent = (presenters[id] || name || 'Peserta') + ' (Presentasi)';
        }
    }

    function removePeerTile(peerId) {
        var tile = document.getElementById('tile-' + peerId);
        if (tile) tile.remove();
        delete remoteStreams[peerId];
    }

    function send(type, payload, recipient) {
        var body = new URLSearchParams({ action:'send', room_id:roomId, signal_type:type, payload:JSON.stringify(payload) });
        if (recipient) body.set('recipient_id', recipient);
        return fetch(signalUrl, { method:'POST', body:body, credentials:'same-origin' });
    }

    function screenRequest(action) {
        return fetch(signalUrl, {
            method:'POST',
            body:new URLSearchParams({action:action, room_id:roomId}),
            credentials:'same-origin'
        }).then(function (response) { return response.json(); });
    }

    setInterval(function () {
        if (sharingScreen) screenRequest('screen_refresh');
    }, 10000);

    function replaceVideoTrack(track) {
        return Promise.all(Object.keys(peers).map(function (peerId) {
            var sender = peers[peerId].getSenders().find(function (item) { return item.track && item.track.kind === 'video'; });
            return sender ? sender.replaceTrack(track) : Promise.resolve();
        }));
    }

    function stopScreenShare() {
        if (!screenStream) return Promise.resolve();
        screenStream.getTracks().forEach(function (track) { track.stop(); });
        screenStream = null;
        sharingScreen = false;
        return replaceVideoTrack(cameraTrack).then(function () {
            addTile('local', localStream, displayName + ' (Anda)');
            document.getElementById('toggleScreen').classList.remove('active');
            document.getElementById('meetingNote').textContent = 'Berbagi layar dihentikan.';
            send('screen_stop', {name:displayName});
            return screenRequest('screen_release');
        });
    }

    function startScreenShare() {
        screenRequest('screen_acquire').then(function (lock) {
            if (!lock.ok) {
                alert('Peserta lain sedang berbagi layar. Tunggu sampai selesai.');
                return;
            }
            return navigator.mediaDevices.getDisplayMedia({
                video: { displaySurface:'browser', cursor:'always' },
                audio: { suppressLocalAudioPlayback:false },
                preferCurrentTab: false,
                selfBrowserSurface: 'include',
                surfaceSwitching: 'include'
            }).then(function (stream) {
                screenStream = stream;
                sharingScreen = true;
                var screenTrack = stream.getVideoTracks()[0];
                return replaceVideoTrack(screenTrack).then(function () {
                    addTile('local', screenStream, displayName + ' (Presentasi)');
                    document.getElementById('toggleScreen').classList.add('active');
                    document.getElementById('meetingNote').textContent = 'Anda sedang berbagi layar. Peserta lain tidak dapat berbagi sampai selesai.';
                    send('screen_start', {name:displayName});
                    screenTrack.onended = stopScreenShare;
                });
            }).catch(function (error) {
                screenRequest('screen_release');
                if (error.name !== 'AbortError') alert('Tab atau layar tidak dapat dibagikan. Pastikan website menggunakan HTTPS dan izinkan akses berbagi layar.');
            });
        });
    }

    function createPeer(peerId, offerer) {
        if (peers[peerId]) return peers[peerId];
        var pc = new RTCPeerConnection({ iceServers:[
            {urls:'stun:stun.l.google.com:19302'},
            {urls:'stun:stun1.l.google.com:19302'},
            {urls:'stun:stun.cloudflare.com:3478'}
        ] });
        peers[peerId] = pc;
        pc.isPolite = myId > peerId;
        pc.makingOffer = false;
        pc.ignoreOffer = false;
        pendingCandidates[peerId] = [];
        localStream.getTracks().forEach(function (track) { pc.addTrack(track, localStream); });
        pc.onicecandidate = function (event) { if (event.candidate) send('ice', event.candidate, peerId); };
        pc.onnegotiationneeded = function () {
            pc.makingOffer = true;
            pc.createOffer().then(function (offer) {
                return pc.setLocalDescription(offer);
            }).then(function () {
                return send('offer', pc.localDescription, peerId);
            }).catch(function () {}).finally(function () {
                pc.makingOffer = false;
            });
        };
        pc.ontrack = function (event) {
            var stream = event.streams[0] || remoteStreams[peerId] || new MediaStream();
            remoteStreams[peerId] = stream;
            if (event.streams.length === 0) stream.addTrack(event.track);
            addTile(peerId, stream, peerId);
        };
        pc.oniceconnectionstatechange = function () {
            if (pc.iceConnectionState === 'failed') {
                pc.restartIce();
            }
            if (pc.iceConnectionState === 'closed') {
                delete peers[peerId];
                removePeerTile(peerId);
            }
        };
        pc.onconnectionstatechange = function () {
            if (pc.connectionState === 'disconnected') {
                setTimeout(function () {
                    if (pc.connectionState === 'disconnected') {
                        pc.close();
                        delete peers[peerId];
                        removePeerTile(peerId);
                        createPeer(peerId, myId < peerId);
                    }
                }, 1500);
            }
            if (pc.connectionState === 'failed') {
                pc.close();
                delete peers[peerId];
                removePeerTile(peerId);
                setTimeout(function () {
                    createPeer(peerId, myId < peerId);
                }, 1500);
            }
        };
        return pc;
    }

    function handleSignal(signal) {
        var payload = JSON.parse(signal.payload);
        var pc;
        if (signal.signal_type === 'join') {
            if (peers[signal.sender_id]) {
                peers[signal.sender_id].close();
                delete peers[signal.sender_id];
                removePeerTile(signal.sender_id);
                pendingCandidates[signal.sender_id] = [];
            }
            if (myId < signal.sender_id) createPeer(signal.sender_id, true);
            return;
        }
        if (signal.signal_type === 'offer') {
            pc = createPeer(signal.sender_id, false);
            var offerCollision = pc.makingOffer || pc.signalingState !== 'stable';
            pc.ignoreOffer = !pc.isPolite && offerCollision;
            if (pc.ignoreOffer) return;
            var setOffer = pc.isPolite && offerCollision
                ? pc.setLocalDescription({type:'rollback'}).then(function () { return pc.setRemoteDescription(payload); })
                : pc.setRemoteDescription(payload);
            setOffer.then(function () {
                return Promise.all((pendingCandidates[signal.sender_id] || []).map(function (candidate) { return pc.addIceCandidate(candidate); }));
            }).then(function () { pendingCandidates[signal.sender_id] = []; return pc.createAnswer(); }).then(function (answer) { return pc.setLocalDescription(answer); }).then(function () { return send('answer', pc.localDescription, signal.sender_id); }).catch(function () {});
        } else if (signal.signal_type === 'answer' && peers[signal.sender_id]) {
            peers[signal.sender_id].setRemoteDescription(payload).then(function () {
                return Promise.all((pendingCandidates[signal.sender_id] || []).map(function (candidate) { return peers[signal.sender_id].addIceCandidate(candidate); }));
            }).then(function () { pendingCandidates[signal.sender_id] = []; });
        } else if (signal.signal_type === 'ice' && peers[signal.sender_id]) {
            if (peers[signal.sender_id].remoteDescription) {
                peers[signal.sender_id].addIceCandidate(payload);
            } else {
                pendingCandidates[signal.sender_id].push(payload);
            }
        } else if (signal.signal_type === 'screen_start') {
            presenters[signal.sender_id] = payload.name || 'Peserta';
            var presenterTile = document.getElementById('tile-' + signal.sender_id);
            if (presenterTile) {
                presenterTile.classList.add('screen-presenter');
                presenterTile.querySelector('.video-name').textContent = (payload.name || 'Peserta') + ' (Presentasi)';
            }
        } else if (signal.signal_type === 'screen_stop') {
            delete presenters[signal.sender_id];
            var stoppedTile = document.getElementById('tile-' + signal.sender_id);
            if (stoppedTile) stoppedTile.classList.remove('screen-presenter');
        }
    }

    function poll() {
        fetch(signalUrl + '?action=list&room_id=' + roomId + '&after_id=' + lastSignalId, {credentials:'same-origin'})
            .then(function (response) { return response.json(); })
            .then(function (data) { (data.signals || []).forEach(function (signal) { lastSignalId = Math.max(lastSignalId, Number(signal.id_signal)); handleSignal(signal); }); })
            .catch(function () {})
            .finally(function () { setTimeout(poll, 1200); });
    }

    navigator.mediaDevices.getUserMedia({
        video: { width:{ideal:640, max:1280}, height:{ideal:360, max:720}, frameRate:{ideal:24, max:30} },
        audio: { echoCancellation:true, noiseSuppression:true, autoGainControl:true }
    }).then(function (stream) {
        localStream = stream;
        cameraTrack = stream.getVideoTracks()[0];
        addTile('local', stream, displayName + ' (Anda)');
        document.getElementById('meetingNote').textContent = 'Meeting aktif. Bagikan halaman ini kepada peserta yang dijadwalkan.';
        attendanceRequest('checkin');
        fetch(signalUrl + '?action=cursor&room_id=' + roomId, {credentials:'same-origin'})
            .then(function (response) { return response.json(); })
            .then(function (cursor) {
                lastSignalId = Number(cursor.last_id || 0);
                return send('join', {name:displayName, refreshed:true});
            })
            .then(function () { poll(); });
    }).catch(function () { document.getElementById('meetingNote').textContent = 'Kamera atau mikrofon tidak dapat diakses. Pastikan HTTPS aktif, izin kamera diberikan, lalu muat ulang.'; });

    document.getElementById('toggleMic').onclick = function () { micEnabled = !micEnabled; localStream.getAudioTracks().forEach(function (track) { track.enabled = micEnabled; }); this.classList.toggle('active', micEnabled); };
    document.getElementById('toggleCamera').onclick = function () { cameraEnabled = !cameraEnabled; localStream.getVideoTracks().forEach(function (track) { track.enabled = cameraEnabled; }); this.classList.toggle('active', cameraEnabled); };
    document.getElementById('toggleScreen').onclick = function () { sharingScreen ? stopScreenShare() : startScreenShare(); };
    document.getElementById('leaveMeeting').onclick = function () { if (sharingScreen) stopScreenShare(); if (localStream) localStream.getTracks().forEach(function (track) { track.stop(); }); Object.values(peers).forEach(function (pc) { pc.close(); }); screenRequest('screen_release'); attendanceRequest('leave'); window.location.href = '../index.php?page=beranda'; };
})();
</script>
</body>
</html>
