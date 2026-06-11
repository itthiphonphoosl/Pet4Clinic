// script.js
function checkAuthorizationForIndex() {
  const tokenAccess = getCookie("tokenAccess"); // Function to get cookie value
  if (tokenAccess) {
    window.location.href =
      "dashboard.html";
  }
}

function checkAuthorization() {
  const tokenAccess = getCookie("tokenAccess"); // Function to get cookie value
  if (!tokenAccess) {
    // Redirect to login page if token is missing
    window.location.href =
      "index.html";
  } else {
    return tokenAccess;
  }
}

function logout() {
  clearCookies();
  checkAuthorization();
}

function formatCurrency(amount) {
  return parseFloat(amount).toLocaleString("en-US", {
    minimumFractionDigits: 2,
  });
}

function formatDateTime(dateTimeString) {
  const months = [
    "ม.ค.",
    "ก.พ.",
    "มี.ค.",
    "เม.ย.",
    "พ.ค.",
    "มิ.ย.",
    "ก.ค.",
    "ส.ค.",
    "ก.ย.",
    "ต.ค.",
    "พ.ย.",
    "ธ.ค.",
  ];

  const date = new Date(dateTimeString);

  // ดึงวันที่, เดือน (จาก array months โดยใช้ index ที่ได้จาก getMonth()), และปี จากวัตถุ Date
  const day = date.getDate(); // ดึงวันที่
  const month = months[date.getMonth()]; // ดึงชื่อเดือนย่อจาก array โดยใช้ index
  const year = date.getFullYear(); // ดึงปี

  // รวมเป็นสตริงตามรูปแบบที่ต้องการ
  return `${day} ${month} ${year}`;
}

function getName() {
  const userInfoString = getCookie("userInfo");
  const userInfo = JSON.parse(userInfoString);
  const name = userInfo.u_name; // Function to get cookie value
  if (name) {
    const capitalizedString = capitalizeFirstLetter(name);
    return "Hi' " + capitalizedString;
  } else {
    return "Pet4Clinic";
  }
}
function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

function getType() {
  const userInfoString = getCookie("userInfo");
  const userInfo = JSON.parse(userInfoString);
  const u_type = userInfo.u_type; // Function to get cookie value
  if (u_type) {
    return u_type;
  } else {
    return "Pet4Clinic";
  }
}

// Function to set cookie
function setCookie(name, value, days) {
  let expires = "";
  if (days) {
    const date = new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    expires = "; expires=" + date.toUTCString();
  }
  document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

// Function to get cookie value by name
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
  return null;
}

function clearCookies() {
  const cookies = document.cookie.split(";");

  for (let i = 0; i < cookies.length; i++) {
    const cookie = cookies[i];
    const eqPos = cookie.indexOf("=");
    const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
    document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
  }
}

function setupMenu() {
  const userInfoString = getCookie("userInfo");
  const userInfo = JSON.parse(userInfoString);
  const menus = userInfo.menus;
  if (menus.length == 0 || menus == null) {
    return;
  }

  // 'm_dashboard' หน้า Dashboard (ALL)
  // 'm_pet' หน้าจัดการผู้ป่วย (ALL)
  // 'm_staff' หน้าจัดการพนักงาน (ADMIN)
  // 'm_veterinary' หน้าจัดการสัตวแพทย์ (ADMIN)
  // 'm_drug' หน้าจัดการยา (ADMIN)
  // 'm_treat_report' หน้าดูรายงานการรักษา (ADMIN)
  // 'm_drug_report' รายงานข้อมูลยา (ADMIN)

  // 'm_treat' หน้าการรักษาคนไข้ (VET)

  // 'm_payment' หน้าเก็บเงิน (ADMIN, STAFF)

  const menuNames = {
    m_dashboard: "แดชบอร์ด",
    m_pet: "ข้อมูลผู้ป่วย",
    m_staff: "ข้อมูลพนักงาน",
    m_veterinary: "ข้อมูลสัตวแพทย์",
    m_drug: "ข้อมูลยา",
    m_treat_report: "รายงานข้อมูลการรักษา ",
    m_drug_report: "รายงานข้อมูลยา",
    m_treat: "ข้อมูลการรักษา",
    m_payment: "ข้อมูลชำระเงิน",
  };

  const menuIcons = {
    m_dashboard: "fas fa-tachometer-alt",
    m_pet: "fas fa-user-injured",
    m_staff: "fas fa-user",
    m_veterinary: "fas fa-user-md",
    m_drug: "fas fa-capsules",
    m_treat_report: "far fa-list-alt",
    m_drug_report: "far fa-list-alt",
    m_treat: "fas fa-notes-medical",
    m_payment: "fas fa-file-invoice-dollar",
  };

  const menuLinks = {
    m_dashboard: "dashboard.html",
    m_pet: "pet.html",
    m_staff: "staff.html",
    m_veterinary: "veterinary.html",
    m_drug: "drug.html",
    m_treat_report: "treat_report.html",
    m_drug_report: "drug_report.html",
    m_treat: "treat.html",
    m_payment: "payment.html",
  };

  let menuHTML = `<ul id="menu" class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">`;

  menus.forEach((menu) => {
    const menuName = menuNames[menu] || "";
    const menuIcon = menuIcons[menu] || "";
    const menuLink = menuLinks[menu] || "#";

    menuHTML += `
      <li class="nav-item">
        <a href="${menuLink}" class="nav-link">
          <i class="nav-icon ${menuIcon}"></i>
          <p>${menuName}</p>
        </a>
      </li>
    `;
  });

  menuHTML += `
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-sign-out-alt"></i>
                  <p onclick="logout()">ออกจากระบบ</p>
                </a>
              </li>`;

  menuHTML += `</ul>`;

  document.getElementById("sys_menu").innerHTML = menuHTML;
}

function checkPermission() {
  const userInfoString = getCookie("userInfo");
  const userInfo = JSON.parse(userInfoString);
  const menus = userInfo.menus;
  if (menus.length == 0 || menus == null) {
    window.location.href =
      "dashboard.html";
  } else {
    var redirectToDashboard = false;
    let path = window.location.pathname;
    if (
      (path.includes("staff.html") && !menus.includes("m_staff")) ||
      (path.includes("staffeditor.html") && !menus.includes("m_staff")) ||
      (path.includes("veterinary.html") && !menus.includes("m_veterinary")) ||
      (path.includes("veterinaryeditor.html") &&
        !menus.includes("m_veterinary")) ||
      (path.includes("drug.html") && !menus.includes("m_drug")) ||
      (path.includes("drugeditor.html") && !menus.includes("m_drug")) ||
      (path.includes("treat_report.html") &&
        !menus.includes("m_treat_report")) ||
      (path.includes("drug_report.html") && !menus.includes("m_drug_report")) ||
      (path.includes("treat.html") && !menus.includes("m_treat")) ||
      (path.includes("treateditor.html") && !menus.includes("m_treat")) ||
      (path.includes("payment.html") && !menus.includes("m_payment"))
    ) {
      redirectToDashboard = true;
    }

    if (redirectToDashboard) {
      window.location.href =
        "dashboard.html";
    }
  }
}

window.onload = function () {
  let path = window.location.pathname;
  if (
    path.endsWith("/backoffice") ||
    path.endsWith("/backoffice/") ||
    path.endsWith("/backoffice/index.html")
  ) {
    checkAuthorizationForIndex();
  } else {
    const logoBackoffice = document.getElementById("logo-backoffice");
    logoBackoffice.src =
      "images/logo.png";
    checkAuthorization();
    document.getElementById("sys_name").innerHTML = getName();
    setupMenu();
    checkPermission();
    const contentWrapper = document.querySelector(".content-wrapper");
    if (contentWrapper) {
      contentWrapper.style.overflow = "auto";
    }
  }
};
