const fetch = require('node-fetch'); // install if not yet: npm install node-fetch
const pdfParse = require('pdf-parse');

(async () => {
  const fileUrl = 'https://plum-actual-elephant-371.mypinata.cloud/ipfs/QmTJvQWb9KeidE2JhKFuauJkF7CkQhArp9diS7LMEQ2CHm';

  console.log('📥 Fetching file...');
  const response = await fetch(fileUrl);
  const buffer = await response.arrayBuffer();

  console.log('📄 Parsing PDF...');
  const data = await pdfParse(Buffer.from(buffer));

  console.log('✅ Extracted text preview:');
  console.log(data.text.slice(0, 500) || '⚠️ No text found');
})();
