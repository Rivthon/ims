(() => {
    const config = window.topologyConfig;
    const svg = document.getElementById('topology-canvas');
    if (!config || !svg) return;

    const namespace = 'http://www.w3.org/2000/svg';
    const nodeMap = new Map(config.nodes.map(node => [node.id, node]));
    const iconMap = {internet:'☁',cloud:'☁',ont:'◈',router:'◆',firewall:'⬟',core_switch:'▦',switch:'▦',poe_switch:'▦',access_point:'◉',server:'▤',pc:'▣',ups:'▥',nvr:'▤',dvr:'▤',camera:'●'};
    const linkElements = [];
    const createSvg = (tag, attributes = {}) => {
        const element = document.createElementNS(namespace, tag);
        Object.entries(attributes).forEach(([key, value]) => element.setAttribute(key, value));
        return element;
    };

    const linksLayer = createSvg('g');
    const nodesLayer = createSvg('g');
    svg.append(linksLayer, nodesLayer);

    config.links.forEach(link => {
        const line = createSvg('line', {class:`topology-link ${link.type} ${link.status}`});
        const label = createSvg('text', {class:'topology-link-label'});
        label.textContent = link.label || link.type;
        linksLayer.append(line, label);
        linkElements.push({link, line, label});
    });

    const updateLinks = () => linkElements.forEach(({link, line, label}) => {
        const source = nodeMap.get(link.source); const target = nodeMap.get(link.target);
        if (!source || !target) return;
        line.setAttribute('x1', source.x); line.setAttribute('y1', source.y);
        line.setAttribute('x2', target.x); line.setAttribute('y2', target.y);
        label.setAttribute('x', (source.x + target.x) / 2);
        label.setAttribute('y', (source.y + target.y) / 2 - 7);
    });

    const showDetail = node => {
        const panel = document.getElementById('node-detail');
        panel.replaceChildren();
        const heading = document.createElement('h3'); heading.textContent = node.name;
        const badge = document.createElement('span'); badge.className = `badge b-${node.status}`; badge.textContent = node.status.toUpperCase();
        const list = document.createElement('dl');
        const values = {Jenis:node.device_type.replaceAll('_',' '),Kode:node.code,IP:node.ip,MAC:node.mac,Port:node.port,Provider:node.provider,SSID:node.ssid,VLAN:node.vlan,Lokasi:node.location,Aset:node.asset,Model:node.model,Serial:node.serial,Channel:node.channel,Resolusi:node.resolution,Recording:node.recording,Retensi:node.retention?`${node.retention} hari`:null,Catatan:node.notes};
        Object.entries(values).filter(([,value]) => value !== null && value !== '').forEach(([key,value]) => {
            const dt = document.createElement('dt'); dt.textContent = key;
            const dd = document.createElement('dd'); dd.textContent = value;
            list.append(dt, dd);
        });
        panel.append(heading, badge, list);
        if (config.canManage) {
            const actions = document.createElement('div');
            actions.className = 'flex';
            const editLink = document.createElement('a');
            editLink.className = 'btn btn-primary';
            editLink.textContent = 'Edit Node';
            editLink.href = `${node.update_url}/edit`;
            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'btn btn-danger';
            deleteButton.textContent = 'Hapus Node';
            deleteButton.addEventListener('click', async () => {
                if (!window.confirm(`Hapus node "${node.name}" beserta seluruh koneksinya?`)) return;
                deleteButton.disabled = true;
                deleteButton.textContent = 'Menghapus...';
                const response = await fetch(node.update_url, {
                    method: 'DELETE',
                    headers: {'Accept':'application/json','X-CSRF-TOKEN':config.csrf},
                });
                if (response.ok) {
                    window.location.reload();
                    return;
                }
                deleteButton.disabled = false;
                deleteButton.textContent = 'Hapus Node';
                window.alert('Node gagal dihapus. Silakan muat ulang dan coba lagi.');
            });
            actions.append(editLink, deleteButton);
            panel.append(actions);
        }
    };

    config.nodes.forEach(node => {
        const group = createSvg('g', {class:`topology-node ${node.status}`, transform:`translate(${node.x} ${node.y})`, tabindex:'0'});
        const box = createSvg('rect', {x:-70,y:-38,width:140,height:76,rx:12});
        const icon = createSvg('text', {class:'node-icon',x:0,y:-14}); icon.textContent = iconMap[node.device_type] || '◆';
        const name = createSvg('text', {class:'node-name',x:0,y:13}); name.textContent = node.name;
        const meta = createSvg('text', {class:'node-meta',x:0,y:29}); meta.textContent = node.ip || node.device_type.replaceAll('_',' ');
        group.append(box, icon, name, meta); nodesLayer.append(group);
        group.addEventListener('click', () => showDetail(node));
        group.addEventListener('keydown', event => { if (event.key === 'Enter') showDetail(node); });

        if (config.canManage) {
            let dragging = false;
            const coordinates = event => { const point = svg.createSVGPoint(); point.x = event.clientX; point.y = event.clientY; return point.matrixTransform(svg.getScreenCTM().inverse()); };
            group.addEventListener('pointerdown', event => { dragging = true; group.classList.add('dragging'); group.setPointerCapture(event.pointerId); event.preventDefault(); });
            group.addEventListener('pointermove', event => { if (!dragging) return; const point = coordinates(event); node.x = Math.max(75, Math.min(1125, point.x)); node.y = Math.max(45, Math.min(635, point.y)); group.setAttribute('transform',`translate(${node.x} ${node.y})`); updateLinks(); });
            group.addEventListener('pointerup', async event => {
                if (!dragging) return; dragging = false; group.classList.remove('dragging'); group.releasePointerCapture(event.pointerId);
                await fetch(node.update_url,{method:'PATCH',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':config.csrf},body:JSON.stringify({position_x:node.x,position_y:node.y})});
            });
        }
    });
    updateLinks();
})();
