const express = require("express");
const axios = require("axios");
const crypto = require("crypto");
const xml2js = require("xml2js");

const app = express();
app.use(express.json());

app.post("/check-password-hash", async (req, res) => {
  const { hex } = req.body;

  if (!hex) {
    return res.status(400).json({ error: "Missing hash value" });
  }

  try {
    // 1. Fetch salt
    const saltResponse = await axios.get(
      "https://testapi.refractionx.com/salt.xml"
    );
    const saltXml = await xml2js.parseStringPromise(saltResponse.data);
    const salt = saltXml?.testdata?.salt?.[0];

    // 2. Fetch password
    const passwordResponse = await axios.get(
      "https://testapi.refractionx.com/password.json"
    );
    const password = passwordResponse.data?.value;

    if (!salt || !password) {
      return res
        .status(500)
        .json({ error: "Invalid data from external services" });
    }

    // 3. Generate hash
    const hash = crypto
      .createHash("sha256")
      .update(password + salt)
      .digest("hex");

    // 4. Compare
    if (hex === hash) {
      return res.sendStatus(204); // No Content
    } else {
      return res.sendStatus(401); // Unauthorized
    }
  } catch (err) {
    console.error(err.message);
    return res.status(500).json({ error: "Internal server error" });
  }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});
