(() => {
    const config = window.topologyConfig;
    const svg = document.getElementById('topology-canvas');
    if (!config || !svg) return;

    const namespace = 'http://www.w3.org/2000/svg';
    const nodeMap = new Map(config.nodes.map(node => [node.id, node]));
    const nodeForm = document.querySelector('form input[name="position_x"]')?.form;
    const nodeFormAction = nodeForm?.action;
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

    const setFieldValue = (name, value) => {
        const field = nodeForm?.elements.namedItem(name);
        if (field) field.value = value ?? '';
    };

    const resetNodeForm = () => {
        if (!nodeForm) return;
        nodeForm.reset();
        nodeForm.action = nodeFormAction;
        nodeForm.querySelector('input[name="_method"]')?.remove();
        setFieldValue('position_x', 180);
        setFieldValue('position_y', 180);
        nodeForm.closest('details').querySelector('summary strong').textContent = '+ Tambah Node Perangkat';
        nodeForm.querySelector('button.btn-primary').textContent = 'Tambah Node';
        nodeForm.querySelector('[data-cancel-edit]')?.remove();
    };

    const startEditing = node => {
        if (!nodeForm) return;
        nodeForm.action = node.update_url;
        let methodField = nodeForm.querySelector('input[name="_method"]');
        if (!methodField) {
            methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            nodeForm.append(methodField);
        }
        methodField.value = 'PUT';
        const editableValues = {name:node.name,device_type:node.device_type,status:node.status,code:node.code,ip_address:node.ip,mac_address:node.mac,port:node.port,provider:node.provider,ssid:node.ssid,vlan:node.vlan,channel:node.channel,resolution:node.resolution,recording_mode:node.recording,retention_days:node.retention,notes:node.notes};
        Object.entries(editableValues).forEach(([name, value]) => setFieldValue(name, value));
        const assetField = nodeForm.elements.namedItem('asset_id');
        const locationField = nodeForm.elements.namedItem('location_id');
        if (assetField) assetField.value = [...assetField.options].find(option => option.textContent.trim().startsWith(`${node.asset} —`))?.value ?? '';
        if (locationField) locationField.value = [...locationField.options].find(option => option.textContent.trim() === node.location)?.value ?? '';
        setFieldValue('position_x', node.x);
        setFieldValue('position_y', node.y);
        const container = nodeForm.closest('details');
        container.open = true;
        container.querySelector('summary strong').textContent = `Edit Node: ${node.name}`;
        const submitButton = nodeForm.querySelector('button.btn-primary');
        submitButton.textContent = 'Simpan Perubahan';
        if (!nodeForm.querySelector('[data-cancel-edit]')) {
            const cancelButton = document.createElement('button');
            cancelButton.type = 'button';
            cancelButton.className = 'btn';
            cancelButton.dataset.cancelEdit = 'true';
            cancelButton.textContent = 'Batal Edit';
            cancelButton.addEventListener('click', resetNodeForm);
            submitButton.insertAdjacentElement('afterend', cancelButton);
        }
        container.scrollIntoView({behavior:'smooth', block:'start'});
        nodeForm.elements.namedItem('name')?.focus();
    };

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
            const editLink = document.createElement('button');
            editLink.type = 'button';
            editLink.className = 'btn btn-primary';
            editLink.textContent = 'Edit Node';
            editLink.addEventListener('click', () => startEditing(node));
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
