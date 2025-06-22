document.addEventListener('DOMContentLoaded', () => {
    // --- SELETORES GLOBAIS ---
    const homeLinkBtn = document.getElementById('home-link-btn');
    const loginScreen = document.getElementById('login-screen');
    const mainScreen = document.getElementById('main-screen');
    const lockScreen = document.getElementById('lock-screen');
    const tfaScreen = document.getElementById('tfa-screen');
    const contentTitle = document.getElementById('content-title');
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');
    
    // Loading Screen
    const loadingScreen = document.getElementById('loading-screen');

    // Selecionar credenciais
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const deleteSelectedBtn = document.getElementById('delete-selected-btn');

    // Hamburguer Botão
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const contentArea = document.querySelector('.content-area');
    
    // Seletores da Tela de Bloqueio
    const lockNowBtn = document.getElementById('lock-now-btn');
    const unlockForm = document.getElementById('unlock-form');
    const lockScreenEmail = document.getElementById('lock-screen-email');
    const unlockError = document.getElementById('unlock-error');
    const logoutFromLockScreenBtn = document.getElementById('logout-from-lock-screen');

    const contentSections = document.querySelectorAll('.content-section');
    const listaCredenciaisContainer = document.getElementById('lista-credenciais-container');
    const tableBody = listaCredenciaisContainer.querySelector('.table-body-custom');

    const showFormNovaCredencialBtn = document.getElementById('show-form-nova-credencial');
    const profileIconTrigger = document.getElementById('profile-icon-trigger');
    const profileMenuDropdown = document.getElementById('profile-menu-dropdown');
    
    // Seletores de Navegação
    const navItems = document.querySelectorAll('.nav-item[data-target]');
    const navConfiguracoes = document.getElementById('nav-configuracoes');

    const logoutBtn = document.getElementById('logout-btn');
    const novoForm = document.getElementById('novo-form');
    const editForm = document.getElementById('edit-form');
    
    const modalExcluir = document.getElementById('modal-excluir-item');
    const confirmarExclusaoBtn = document.getElementById('confirmar-exclusao-btn');
    const cancelarExclusaoBtn = document.getElementById('cancelar-exclusao-btn');
    
    // Seletores da página "Minha Conta"
    const minhaContaForm = document.getElementById('minha-conta-form');
    const alterarEmailForm = document.getElementById('alterar-email-form');
    const enable2faBtn = document.getElementById('enable-2fa-btn');
    const setup2faSection = document.getElementById('2fa-setup-section');
    const qrCodeImg = document.getElementById('qr-code-img');
    const manualSecretKey = document.getElementById('manual-secret-key');
    const verify2faForm = document.getElementById('2fa-verify-form');
    const tfaLoginForm = document.getElementById('tfa-login-form');
    const tfaError = document.getElementById('tfa-error');
    const tfaBackButton = document.getElementById('tfa-back-to-login-btn');
    
    // Remover 2FA
    const remove2faBtn = document.getElementById('remove-2fa-btn');
    const remove2faModal = document.getElementById('modal-remover-2fa');
    const remove2faForm = document.getElementById('remove-2fa-form');

    // Variáveis de Estado
    let itemIdParaExcluir = null;
    let currentUserEmail = '';
    let currentUserName = '';

    // --- FUNÇÕES DE LÓGICA DE ESTADO E SESSÃO ---

    function updateUserProfile(nome, email, tfaEnabled) {
        currentUserName = nome || '';
        currentUserEmail = email || '';
        
        
        document.getElementById('mc-nome-usuario').value = currentUserName;
        document.getElementById('mc-email').value = currentUserEmail;

        let initial = '..';
        if (currentUserName) {
            const names = currentUserName.split(' ');
            initial = names[0].substring(0, 1);
            if (names.length > 1) {
                initial += names[names.length - 1].substring(0, 1);
            }
        } else if (currentUserEmail) {
            initial = currentUserEmail.substring(0, 2);
        }
        profileIconTrigger.textContent = initial.toUpperCase();

    // ** LÓGICA PARA ALTERNAR OS BOTÕES 2FA **
    if (tfaEnabled) {
        enable2faBtn.style.display = 'none';
        remove2faBtn.style.display = 'inline-flex';
    } else {
        enable2faBtn.style.display = 'inline-flex';
        remove2faBtn.style.display = 'none';
        setup2faSection.style.display = 'none'; // Esconde a seção de setup se 2FA for desativado
    }
}

    const checkUserSession = async () => {
        try {
            const response = await fetch('backend/check_session.php');
            const result = await response.json();
            if (result.status === 'success' && result.loggedin) {
                updateUserProfile(result.nome, result.email, result.tfa_enabled);
                if (sessionStorage.getItem('isLocked') === 'true') {
                    showLockScreen(result.email);
                } else {
                    showMainScreen(result.email, result.nome);
                }
            } else {
                showLoginScreen();
            }
        } catch (error) {
            console.error("Erro ao verificar sessão:", error);
                    // Em caso de erro, também mostra a tela de login
        showLoginScreen();
     } finally {
        hideLoader();
     }
    };

    // Adicione estas duas funções ao seu script

    function showLoader() {
        const loader = document.getElementById('loading-screen');
        if (loader) {
        // Usa 'display: flex' para ativar a tela, pois ela tem essa propriedade quando está ativa
        loader.style.display = 'flex';
        }
    }

    function hideLoader() {
        const loader = document.getElementById('loading-screen');
        if (loader) {
        loader.style.display = 'none';
        }
    }


    const showLoginScreen = () => {
        loginScreen.classList.add('active');
        mainScreen.classList.remove('active');
        lockScreen.classList.remove('active');
        tfaScreen.classList.remove('active');
    };

    const showMainScreen = (userEmail, userName) => {
        updateUserProfile(userName, userEmail);
        loginScreen.classList.remove('active');
        mainScreen.classList.add('active');
        lockScreen.classList.remove('active');
        tfaScreen.classList.remove('active');
        switchContentSection('lista-credenciais-container', '🔐 Meu cofre');
        carregarCredenciais();
    };
    
    const showLockScreen = (email) => {
        currentUserEmail = email;
        mainScreen.classList.remove('active');
        loginScreen.classList.remove('active');
        lockScreen.classList.add('active');
        tfaScreen.classList.remove('active');
        lockScreenEmail.textContent = email;
        unlockError.style.display = 'none';
        document.getElementById('unlock-password').value = '';
        closeAllDropdowns();
    };

    const doLogout = async () => {
        sessionStorage.removeItem('isLocked');
        try {
            await fetch('backend/logout.php');
            window.location.reload();
        } catch (error) {
            console.error("Erro no logout:", error);
            alert("Não foi possível encerrar a sessão.");
        }
    };

    // --- LISTENERS DE EVENTOS GERAIS E NAVEGAÇÃO ---

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        showLoader();
        loginError.style.display = 'none';

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        try {
            const response = await fetch('backend/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            const result = await response.json();

            if (result.status === 'success') {
                await checkUserSession();
            } else if (result.status === '2fa_required') {
            loginScreen.classList.remove('active');
            if(tfaScreen) tfaScreen.classList.add('active');
            } else {
                loginError.textContent = result.message || 'Erro desconhecido.';
                loginError.style.display = 'block';
            }
        } catch (error) {
            loginError.textContent = 'Erro de comunicação com o servidor.';
            loginError.style.display = 'block';
        } finally {
        hideLoader(); // <--- ESCONDE O LOADING NO FINAL (sucesso ou erro)
        }
    });

    if (tfaLoginForm) {
        tfaLoginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            tfaError.style.display = 'none';
            const tfa_code = document.getElementById('tfa-code-login').value;
            try {
                const response = await fetch('backend/verificar_login_2fa.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tfa_code })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    tfaScreen.classList.remove('active');
                    checkUserSession(); 
                } else {
                    tfaError.textContent = result.message || 'Erro desconhecido.';
                    tfaError.style.display = 'block';
                }
            } catch (error) {
                tfaError.textContent = 'Erro de comunicação com o servidor.';
                tfaError.style.display = 'block';
            }
        });
    }

    if (tfaBackButton) {
        tfaBackButton.addEventListener('click', () => {
            tfaScreen.classList.remove('active');
            loginScreen.classList.add('active');
        });
    }

    logoutBtn.addEventListener('click', doLogout);
    logoutFromLockScreenBtn.addEventListener('click', doLogout);

    lockNowBtn.addEventListener('click', (e) => {
        e.preventDefault();
        sessionStorage.setItem('isLocked', 'true');
        showLockScreen(currentUserEmail);
    });

    unlockForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        unlockError.style.display = 'none';
        const password = document.getElementById('unlock-password').value;
        try {
            const response = await fetch('backend/reauthenticate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ password })
            });
            const result = await response.json();
            if (result.status === 'success') {
                sessionStorage.removeItem('isLocked');
                checkUserSession();
            } else {
                unlockError.textContent = result.message || "Senha mestra incorreta.";
                unlockError.style.display = 'block';
            }
        } catch (error) {
            unlockError.textContent = "Erro de comunicação com o servidor.";
        }
    });

    if (homeLinkBtn) {
        homeLinkBtn.addEventListener('click', (e) => {
            e.preventDefault();
            switchContentSection('lista-credenciais-container', '🔐 Meu cofre');
            document.querySelectorAll('.sidebar-nav .nav-item').forEach(nav => nav.classList.remove('active'));
            document.querySelector('.nav-item[data-target="lista-credenciais-container"]').classList.add('active');
        });
    }
    
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = item.dataset.target;
            if (targetId) {
                const newTitle = item.textContent.trim();
                switchContentSection(targetId, newTitle);
                
                document.querySelectorAll('.sidebar-nav .nav-item').forEach(nav => nav.classList.remove('active'));
                const parentLi = item.closest('.sidebar-nav .nav-item');
                if (parentLi) {
                    parentLi.classList.add('active');
                }
            }
        });
    });

    if (navConfiguracoes) {
        const parentLink = navConfiguracoes.querySelector('a');
        parentLink.addEventListener('click', (e) => {
            if (e.currentTarget.parentElement.classList.contains('has-submenu')) {
                e.preventDefault();
                e.stopPropagation();
                navConfiguracoes.classList.toggle('open');
            }
        });
    }
    
    profileIconTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        const currentDisplay = profileMenuDropdown.style.display;
        closeAllDropdowns();
        profileMenuDropdown.style.display = currentDisplay === 'block' ? 'none' : 'block';
    });
    
    document.addEventListener('click', () => closeAllDropdowns());
    
    showFormNovaCredencialBtn.addEventListener('click', () => {
        novoForm.reset();
        switchContentSection('form-nova-credencial-container', 'Criar nova credencial');
    });

    // --- LÓGICA DE CREDENCIAIS ---

    const carregarCredenciais = async () => {
        try {
            const response = await fetch('backend/listar_credenciais.php');
            const result = await response.json();
            tableBody.innerHTML = '';
            if (result.status === 'success' && result.data.length > 0) {
                result.data.forEach(item => {
                    const row = document.createElement('div');
                    row.className = 'table-row-custom';
                    row.dataset.id = item.id;
                    row.dataset.nome = item.nome;
                    row.dataset.login = item.login;
                    row.dataset.site = item.site;
                    row.innerHTML = `
                        <input type="checkbox" class="credential-checkbox" />
                         <span>${item.nome}</span>
                        <span>${item.login}</span>
                         <div class="actions-menu">
                             <button class="action-btn">&#8942;</button>
                             <div class="actions-dropdown" style="display:none;">
                                <a href="#" class="view-credential-btn">👀 Visualizar</a>
                                <a href="#" class="copy-password-btn">📋 Copiar Senha</a>
                                <a href="#" class="edit-credential-btn">✏️ Editar</a>
                                <a href="#" class="delete-credential-btn delete-action">🗑️ Excluir</a>
                            </div>
                        </div>
                    `;
                    tableBody.appendChild(row);
                });
            }
            updateEmptyListMessage();
            updateBulkActionUI();
        } catch (error) {
            console.error("Erro ao carregar credenciais:", error);
        }
    };
    
    function switchContentSection(targetSectionId, newTitle) {
        contentSections.forEach(section => { section.style.display = 'none'; });
        const targetSection = document.getElementById(targetSectionId);
        if (targetSection) {
            targetSection.style.display = 'block';
            if (contentTitle) contentTitle.textContent = newTitle;
        }
        closeAllDropdowns();
    }
    
    function updateEmptyListMessage() {
        const emptyMessage = listaCredenciaisContainer.querySelector('.empty-list-message');
        const items = tableBody.querySelectorAll('.table-row-custom');
        emptyMessage.style.display = items.length === 0 ? 'block' : 'none';
    }

    function populateAndShowEditForm(id, nome, login, site) {
        switchContentSection('form-editar-credencial-container', 'Editar credencial');
        document.getElementById('edit-credential-id').value = id;
        document.getElementById('edit-nome-item').value = nome;
        document.getElementById('edit-login').value = login;
        document.getElementById('edit-site').value = site || '';
        const passwordInput = document.getElementById('edit-senha');
        passwordInput.value = '';
        passwordInput.type = 'password';
        passwordInput.placeholder = 'Nova senha (deixe em branco para não alterar)';
    }

    listaCredenciaisContainer.addEventListener('click', async (e) => {
        const link = e.target.closest('a');
        if (link) e.preventDefault(); 

        if (e.target.classList.contains('action-btn') || e.target.closest('.action-btn')) {
            e.stopPropagation();
            const dropdown = e.target.closest('.actions-menu').querySelector('.actions-dropdown');
            const currentDisplay = dropdown.style.display;
            closeAllDropdowns();
            dropdown.style.display = currentDisplay === 'block' ? 'none' : 'block';
            return;
        }

        const row = e.target.closest('.table-row-custom');
        if (!row) return;

        const { id, nome, login, site } = row.dataset;

        if (e.target.closest('.edit-credential-btn')) {
            populateAndShowEditForm(id, nome, login, site);
            return;
        }
        if (e.target.closest('.delete-credential-btn')) {
            itemIdParaExcluir = id;
            abrirModalExclusao();
            return;
        }
        if (e.target.closest('.view-credential-btn')) {
            populateAndShowEditForm(id, nome, login, site);
            const passwordInput = document.getElementById('edit-senha');
            passwordInput.placeholder = 'Carregando senha...';
            try {
                const response = await fetch('backend/visualizar_senha.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id }) });
                const result = await response.json();
                if (result.status === 'success') {
                    passwordInput.value = result.password;
                    passwordInput.type = 'password'; 
                } else {
                    passwordInput.placeholder = 'Falha ao carregar a senha.';
                    alert('Erro: ' + result.message);
                }
            } catch (error) {
                console.error("Erro ao buscar senha:", error);
                passwordInput.placeholder = 'Erro de comunicação.';
            }
            return;
        }
        if (e.target.closest('.copy-password-btn')) {
            const credentialId = row.dataset.id;
            try {
                const response = await fetch('backend/visualizar_senha.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: credentialId }) });
                const result = await response.json();
                if (result.status === 'success') {
                    await navigator.clipboard.writeText(result.password);
                    alert('Senha copiada para a área de transferência!');
                } else {
                    alert('Erro ao obter senha: ' + result.message);
                }
            } catch (error) {
                console.error("Erro ao copiar senha:", error);
                alert("Erro de comunicação ao tentar copiar a senha.");
            }
            return;
        }
    });

    novoForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new URLSearchParams();
        formData.append('nome', document.getElementById('novo-nome-item').value);
        formData.append('login', document.getElementById('novo-login').value);
        formData.append('senha', document.getElementById('novo-senha').value);
        formData.append('site', document.getElementById('novo-site').value);
        try {
            const response = await fetch('backend/salvar_senha.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                alert(result.message);
                switchContentSection('lista-credenciais-container', '🔐 Meu cofre');
                carregarCredenciais();
            } else {
                alert(result.message || 'Erro ao salvar.');
            }
        } catch (error) {
            console.error("Erro no fetch (nova credencial):", error);
        }
    });

    editForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const passwordInput = document.getElementById('edit-senha');
        const originalType = passwordInput.type;
        passwordInput.type = 'password';
        const passwordValue = passwordInput.value;
        passwordInput.type = originalType;
        const id = document.getElementById('edit-credential-id').value;
        const data = { id, nome: document.getElementById('edit-nome-item').value, login: document.getElementById('edit-login').value, site: document.getElementById('edit-site').value, senha: passwordValue };
        try {
            const response = await fetch('backend/atualizar_senha.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
            const result = await response.json();
            alert(result.message);
            if (result.status === 'success' || result.status === 'info') {
                switchContentSection('lista-credenciais-container', '🔐 Meu cofre');
                carregarCredenciais();
            }
        } catch (error) {
            console.error("Erro no fetch (editar credencial):", error);
        }
    });

    // --- LÓGICA DO MODAL E OUTROS ---

    editForm.querySelector('.delete-from-edit-btn').addEventListener('click', () => {
        itemIdParaExcluir = document.getElementById('edit-credential-id').value;
        if(itemIdParaExcluir) abrirModalExclusao();
    });

    confirmarExclusaoBtn.addEventListener('click', async () => {
        if (!itemIdParaExcluir) return;
        try {
            const response = await fetch('backend/excluir_senha.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: itemIdParaExcluir }) });
            const result = await response.json();
            alert(result.message);
            if (result.status === 'success') {
                fecharModalExclusao();
                if (document.getElementById('form-editar-credencial-container').style.display === 'block') {
                    switchContentSection('lista-credenciais-container', '🔐 Meu cofre');
                }
                carregarCredenciais();
            }
        } catch (error) {
            console.error("Erro no fetch (excluir credencial):", error);
        }
    });

    function abrirModalExclusao() { if (modalExcluir) modalExcluir.style.display = 'flex'; }
    function fecharModalExclusao() { if (modalExcluir) modalExcluir.style.display = 'none'; itemIdParaExcluir = null; }
    cancelarExclusaoBtn.addEventListener('click', fecharModalExclusao);
    if (modalExcluir) {
        modalExcluir.querySelector('.modal-close-btn')?.addEventListener('click', fecharModalExclusao);
        modalExcluir.addEventListener('click', (e) => { if (e.target === modalExcluir) fecharModalExclusao(); });
    }
    
    document.querySelectorAll('.cancel-form-btn').forEach(btn => {
        btn.addEventListener('click', () => { switchContentSection('lista-credenciais-container', '🔐 Meu cofre'); });
    });

    function closeAllDropdowns() {
        if (profileMenuDropdown) profileMenuDropdown.style.display = 'none';
        document.querySelectorAll('.actions-dropdown').forEach(dd => dd.style.display = 'none');
    }
    
    const rememberLink = document.querySelector('#login-screen .link');
    const emailLoginInput = document.getElementById('email');
    if (localStorage.getItem('savedEmail')) { emailLoginInput.value = localStorage.getItem('savedEmail'); }
    rememberLink.addEventListener('click', (e) => {
        e.preventDefault();
        if (emailLoginInput.value) { localStorage.setItem('savedEmail', emailLoginInput.value); alert('E-mail salvo para o próximo acesso!'); }
    });

    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', () => {
            const wrapper = button.closest('.password-wrapper');
            const input = wrapper.querySelector('input');
            if (input) { input.type = input.type === "password" ? "text" : "password"; }
        });
    });

    const editPasswordInput = document.getElementById('edit-senha');
    if (editPasswordInput) {
        editPasswordInput.addEventListener('copy', (e) => {
            e.preventDefault();
            const password = e.target.value;
            if (password) {
                navigator.clipboard.writeText(password)
                    .then(() => { alert('Senha copiada para a área de transferência!'); })
                    .catch(err => { console.error('Falha ao copiar a senha: ', err); });
            }
        });
    }

    function formatUrlOnBlur(e) {
        const input = e.target;
        let url = input.value.trim();
        if (url && !/^https?:\/\//i.test(url)) { input.value = 'https://' + url; }
    }
    document.getElementById('novo-site').addEventListener('blur', formatUrlOnBlur);
    document.getElementById('edit-site').addEventListener('blur', formatUrlOnBlur);
    
    // --- LÓGICA MINHA CONTA & 2FA ---
    if (minhaContaForm) {
        minhaContaForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const nome = document.getElementById('mc-nome-usuario').value;
            try {
                const response = await fetch('backend/atualizar_perfil.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ nome }) });
                const result = await response.json();
                alert(result.message);
                if (result.status === 'success') {
                    updateUserProfile(nome, currentUserEmail, sessionStorage.getItem('tfa_enabled') === 'true'); // Passa o estado atual
                }
            } catch (error) {
                console.error('Erro ao atualizar perfil:', error);
            }
        });
    }
    
    if (alterarEmailForm) {
        alterarEmailForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const master_password = document.getElementById('alterar-email-senha').value;
            const new_email = document.getElementById('novo-e-mail').value;
            try {
                const response = await fetch('backend/alterar_email.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ master_password, new_email }) });
                const result = await response.json();
                alert(result.message);
                if (result.status === 'success') { window.location.reload(); }
            } catch (error) {
                console.error('Erro ao alterar e-mail:', error);
            }
        });
    }

    if (enable2faBtn) {
        enable2faBtn.addEventListener('click', async () => {
            try {
                const response = await fetch('backend/gerar_2fa.php');
                const result = await response.json();
                if (result.status === 'success') {
                    qrCodeImg.src = result.qrCodeDataUri;
                    manualSecretKey.textContent = result.secret;
                    setup2faSection.style.display = 'block';
                    enable2faBtn.style.display = 'none';
                } else {
                    alert('Erro: ' + result.message);
                }
            } catch (error) {
                console.error('Erro ao gerar 2FA:', error);
            }
        });
    }

    if (verify2faForm) {
        verify2faForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const tfa_code = document.getElementById('tfa-code').value;
            try {
                const response = await fetch('backend/ativar_2fa.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ tfa_code }) });
                const result = await response.json();
                alert(result.message);
                if (result.status === 'success') {
                    setup2faSection.style.display = 'none';
                    enable2faBtn.textContent = "2FA Ativado";
                    enable2faBtn.disabled = true;
                    enable2faBtn.style.display = 'inline-flex';
                }
            } catch (error) {
                console.error('Erro ao ativar 2FA:', error);
            }
        });
    }

    if (remove2faBtn) {
        remove2faBtn.addEventListener('click', () => {
            remove2faModal.style.display = 'flex';
        });
    }

    // Listener para o botão de cancelar/fechar do modal
    if (remove2faModal) {
        remove2faModal.querySelector('.modal-close-btn').addEventListener('click', () => remove2faModal.style.display = 'none');
        remove2faModal.querySelector('.modal-cancel-btn').addEventListener('click', () => remove2faModal.style.display = 'none');
    }

    // Listener para o envio do formulário de remoção
    if (remove2faForm) {
        remove2faForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const password = document.getElementById('remove-2fa-password').value;
            const errorP = document.getElementById('remove-2fa-error');
            errorP.style.display = 'none';

            try {
                const response = await fetch('backend/remover_2fa.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ master_password: password })
                });
                const result = await response.json();
                
                if (result.status === 'success') {
                    alert(result.message);
                    remove2faModal.style.display = 'none';
                    updateUserProfile(currentUserName, currentUserEmail, false); // Atualiza a UI
                } else {
                    errorP.textContent = result.message;
                    errorP.style.display = 'block';
                }
            } catch(error) {
                errorP.textContent = 'Erro de comunicação com o servidor.';
                errorP.style.display = 'block';
            }
        });
    }

    if (hamburgerBtn) {
    hamburgerBtn.addEventListener('click', (e) => {
        // Impede que o clique "suba" para o contentArea e outros elementos pais.
        e.stopPropagation(); 
        
        document.body.classList.toggle('sidebar-open');
    });
    }


   
    if (contentArea) {
        contentArea.addEventListener('click', () => {
        // Se a sidebar estiver aberta, fecha ela ao clicar no conteúdo
        if (document.body.classList.contains('sidebar-open')) {
            document.body.classList.remove('sidebar-open');
        }
     });
    }


    // Adicione esta nova função ao seu script
    function updateBulkActionUI() {
    if (!tableBody || !selectAllCheckbox || !deleteSelectedBtn) return;

    const itemCheckboxes = tableBody.querySelectorAll('input[type="checkbox"]');
    const checkedItems = tableBody.querySelectorAll('input[type="checkbox"]:checked');

    // Mostra ou esconde o botão de excluir selecionados
    if (checkedItems.length > 0) {
        deleteSelectedBtn.style.display = 'inline-flex';
     } else {
        deleteSelectedBtn.style.display = 'none';
     }

    // Atualiza o estado do checkbox "Todos"
    if (itemCheckboxes.length > 0 && checkedItems.length === itemCheckboxes.length) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
     } else if (checkedItems.length > 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
     } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
     }
    }


    // Listener para o checkbox "Todos"
if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', () => {
        const itemCheckboxes = tableBody.querySelectorAll('.credential-checkbox');
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateBulkActionUI();
    });
}

// Listener para os checkboxes individuais (usando delegação de eventos)
if (tableBody) {
    tableBody.addEventListener('change', (e) => {
        if (e.target.matches('.credential-checkbox')) {
            updateBulkActionUI();
        }
    });
}

// Listener para o botão "Excluir Selecionados"
if (deleteSelectedBtn) {
    deleteSelectedBtn.addEventListener('click', async () => {
        const checkedItems = tableBody.querySelectorAll('.credential-checkbox:checked');
        const idsToDelete = Array.from(checkedItems).map(cb => cb.closest('.table-row-custom').dataset.id);

        if (idsToDelete.length === 0) {
            alert('Nenhum item selecionado.');
            return;
        }

        if (confirm(`Tem certeza que deseja excluir os ${idsToDelete.length} itens selecionados?`)) {
            try {
                const response = await fetch('backend/excluir_varios.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids: idsToDelete })
                    });
                const result = await response.json();
                alert(result.message);
                if (result.status === 'success') {
                    carregarCredenciais(); // Recarrega a lista
                  }
               } catch (error) {
                console.error('Erro ao excluir itens:', error);
                alert('Erro de comunicação com o servidor.');
                }
          }
        });
    }

    // --- INICIALIZAÇÃO ---
    checkUserSession();
});