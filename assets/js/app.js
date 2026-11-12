const form = document.getElementById('form-categorie');
const formSection = document.getElementById('section-form');
const tableBody = document.getElementById('table-categories-body');
const submitButton = document.getElementById('btn-submit');
const toast = document.getElementById('toast');
const searchInput = document.getElementById('product-search');
const sizeFilter = document.getElementById('size-filter');
const productCount = document.getElementById('total-products');
const catalogValue = document.getElementById('catalog-value');
const catalogSubtitle = document.getElementById('catalog-subtitle');
const productIdInput = document.getElementById('cat-id');
const productNameInput = document.getElementById('cat-nom');
const productPriceInput = document.getElementById('cat-couleur');
const productSizeInput = document.getElementById('cat-icone');
const sidebar = document.querySelector('.sidebar');
const mobileMenu = document.querySelector('.mobile-menu');
const products = [];
let toastTimeout;

const currency = new Intl.NumberFormat('en-US', {
  style: 'currency',
  currency: 'USD',
  maximumFractionDigits: 2
});

const showToast = (message, isError = false) => {
  window.clearTimeout(toastTimeout);
  toast.textContent = message;
  toast.classList.toggle('error', isError);
  toast.classList.remove('hidden');
  toastTimeout = window.setTimeout(() => toast.classList.add('hidden'), 3500);
};

const request = async (url, options = {}) => {
  const response = await fetch(url, options);
  const result = await response.json();
  if (!response.ok || result.success === false) {
    throw new Error(result.message || 'The request could not be completed.');
  }
  return result;
};

const openForm = (product = null) => {
  form.reset();
  productIdInput.value = product ? product.id : '';
  productNameInput.value = product ? product.name : '';
  productPriceInput.value = product ? product.price : '';
  productSizeInput.value = product ? product.size : '';
  document.getElementById('form-title').textContent = product ? 'Edit product' : 'Add a product';
  document.getElementById('form-description').textContent = product
    ? 'Update the details for this piece.'
    : 'Give your next piece a place in the collection.';
  formSection.classList.remove('hidden');
  productNameInput.focus();
};

const closeForm = () => {
  formSection.classList.add('hidden');
  form.reset();
  productIdInput.value = '';
};

const makeCell = (text, className = '') => {
  const cell = document.createElement('td');
  if (className) cell.className = className;
  cell.textContent = text;
  return cell;
};

const renderProducts = () => {
  const query = searchInput.value.trim().toLocaleLowerCase();
  const selectedSize = sizeFilter.value;
  const visibleProducts = products.filter((product) => {
    const matchesText = product.name.toLocaleLowerCase().includes(query);
    return matchesText && (!selectedSize || product.size === selectedSize);
  });

  productCount.textContent = String(products.length);
  catalogValue.textContent = currency.format(products.reduce((sum, product) => sum + Number(product.price), 0));
  catalogSubtitle.textContent = `${visibleProducts.length} of ${products.length} ${products.length === 1 ? 'product' : 'products'} shown`;
  tableBody.replaceChildren();

  if (visibleProducts.length === 0) {
    const row = document.createElement('tr');
    const cell = document.createElement('td');
    cell.colSpan = 4;
    cell.className = 'table-message';
    const heading = document.createElement('strong');
    heading.textContent = products.length ? 'No matching products' : 'Your collection is waiting';
    const message = document.createElement('span');
    message.textContent = products.length
      ? 'Try another search or size filter.'
      : 'Add your first product to get started.';
    cell.append(heading, message);
    row.append(cell);
    tableBody.append(row);
    return;
  }

  visibleProducts.forEach((product) => {
    const row = document.createElement('tr');
    const productCell = document.createElement('td');
    const productInfo = document.createElement('div');
    const thumb = document.createElement('span');
    const details = document.createElement('div');
    const name = document.createElement('div');
    const id = document.createElement('div');

    productInfo.className = 'product-cell';
    thumb.className = 'product-thumb';
    thumb.setAttribute('aria-hidden', 'true');
    thumb.textContent = product.name.trim().charAt(0).toLocaleUpperCase() || 'P';
    details.className = 'product-details';
    name.className = 'product-name';
    name.textContent = product.name;
    id.className = 'product-id';
    id.textContent = `ID ${product.id}`;
    details.append(name, id);
    productInfo.append(thumb, details);
    productCell.append(productInfo);
    row.append(productCell);

    const priceCell = makeCell(currency.format(Number(product.price)), 'price');
    row.append(priceCell);

    const sizeCell = document.createElement('td');
    const size = document.createElement('span');
    size.className = 'size-badge';
    size.textContent = product.size;
    sizeCell.append(size);
    row.append(sizeCell);

    const actionsCell = document.createElement('td');
    const actions = document.createElement('div');
    actions.className = 'actions';
    const editButton = document.createElement('button');
    editButton.type = 'button';
    editButton.className = 'icon-button';
    editButton.dataset.action = 'edit';
    editButton.dataset.id = product.id;
    editButton.setAttribute('aria-label', `Edit ${product.name}`);
    editButton.title = 'Edit product';
    editButton.textContent = 'Edit';
    const deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.className = 'icon-button danger';
    deleteButton.dataset.action = 'delete';
    deleteButton.dataset.id = product.id;
    deleteButton.setAttribute('aria-label', `Delete ${product.name}`);
    deleteButton.title = 'Delete product';
    deleteButton.textContent = 'Delete';
    actions.append(editButton, deleteButton);
    actionsCell.append(actions);
    row.append(actionsCell);
    tableBody.append(row);
  });
};

const loadProducts = async () => {
  tableBody.innerHTML = '<tr><td class="table-message" colspan="4">Loading your collection…</td></tr>';
  try {
    const result = await request('api.php');
    if (!Array.isArray(result)) {
      throw new Error('The server returned an invalid product list.');
    }
    products.splice(0, products.length, ...result);
    renderProducts();
  } catch (error) {
    tableBody.innerHTML = '<tr><td class="table-message" colspan="4"><strong>Could not load products</strong>Check your connection and try again.</td></tr>';
    showToast(error.message || 'Error loading products.', true);
  }
};

document.getElementById('btn-show-form').addEventListener('click', () => openForm());
document.getElementById('btn-cancel-form').addEventListener('click', closeForm);
document.getElementById('btn-close-form').addEventListener('click', closeForm);
mobileMenu.addEventListener('click', () => {
  const isOpen = sidebar.classList.toggle('mobile-open');
  mobileMenu.setAttribute('aria-expanded', String(isOpen));
});
sidebar.addEventListener('click', (event) => {
  if (event.target.closest('.nav-link')) {
    sidebar.classList.remove('mobile-open');
    mobileMenu.setAttribute('aria-expanded', 'false');
  }
});
formSection.addEventListener('click', (event) => {
  if (event.target === formSection) closeForm();
});
document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && !formSection.classList.contains('hidden')) closeForm();
});
searchInput.addEventListener('input', renderProducts);
sizeFilter.addEventListener('change', renderProducts);

form.addEventListener('submit', async (event) => {
  event.preventDefault();
  if (!form.reportValidity()) return;

  const id = productIdInput.value;
  const payload = {
    name: productNameInput.value.trim(),
    price: productPriceInput.value,
    size: productSizeInput.value
  };
  const method = id ? 'PUT' : 'POST';
  const url = id ? `api.php?id=${encodeURIComponent(id)}` : 'api.php';

  submitButton.disabled = true;
  submitButton.textContent = id ? 'Saving changes…' : 'Adding product…';
  try {
    await request(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    closeForm();
    showToast(id ? 'Product updated.' : 'Product added to your collection.');
    await loadProducts();
  } catch (error) {
    showToast(error.message || 'Unable to save product.', true);
  } finally {
    submitButton.disabled = false;
    submitButton.textContent = 'Save product';
  }
});

tableBody.addEventListener('click', async (event) => {
  if (!(event.target instanceof Element)) return;
  const button = event.target.closest('button[data-action]');
  if (!button) return;

  const product = products.find((item) => String(item.id) === button.dataset.id);
  if (!product) return;

  if (button.dataset.action === 'edit') {
    openForm(product);
    return;
  }

  if (button.dataset.action === 'delete' && window.confirm(`Delete "${product.name}" from your collection?`)) {
    button.disabled = true;
    try {
      await request(`api.php?id=${encodeURIComponent(product.id)}`, { method: 'DELETE' });
      showToast('Product deleted.');
      await loadProducts();
    } catch (error) {
      button.disabled = false;
      showToast(error.message || 'Unable to delete product.', true);
    }
  }
});

loadProducts();
