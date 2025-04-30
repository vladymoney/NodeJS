const express = require("express");
const crypto = require("crypto");
const fetch = require("node-fetch");
const xml2js = require("xml2js");

const app = express();
app.use(express.json());

app.post("/check-password-hash", async (req, res) => {
  const { hex } = req.body;

  if (!hex || typeof hex !== "string") {
    return res.status(400).json({ error: "Missing or invalid 'hex' value." });
  }

  try {
    const saltResponse = await fetch(
      "https://testapi.refractionx.com/salt.xml"
    );
    const saltText = await saltResponse.text();

    const saltParsed = await xml2js.parseStringPromise(saltText);
    const salt = saltParsed?.testdata?.salt?.[0];

    const passwordResponse = await fetch(
      "https://testapi.refractionx.com/password.json"
    );
    const passwordJson = await passwordResponse.json();
    const password = passwordJson?.value;

    if (!salt || !password) {
      return res
        .status(500)
        .json({ error: "Invalid response from remote services." });
    }

    const computedHash = crypto
      .createHash("sha256")
      .update(password + salt)
      .digest("hex");

    if (computedHash === hex.toLowerCase()) {
      return res.status(204).send();
    } else {
      return res.status(401).json({ error: "Hash mismatch." });
    }
  } catch (error) {
    console.error("Error:", error);
    return res.status(500).json({ error: "Internal server error." });
  }
});

app.listen(3000, () => {
  console.log("Server running at http://localhost:3000");
});
