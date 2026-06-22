document.addEventListener('DOMContentLoaded', () => {
    const stars = document.querySelectorAll('.star_rating .star');
    const submitBtn = document.getElementById('submit_star');
    let selectedRating = 0;
    if (stars.length > 0 && submitBtn) {
        stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                selectedRating = index + 1; 
                stars.forEach((s, i) => {
                    if (i <= index) {
                        s.classList.add('filled');
                        s.classList.add('active'); 
                        s.innerText = '★'; 
                    } else {
                        s.classList.remove('filled');
                        s.classList.remove('active');
                        s.innerText = '★'; 
                    }
                });
                // 평점 선택 후 제출 버튼 활성화
                submitBtn.disabled = false;
                submitBtn.classList.add('active');
            });
        });
        // 제출 버튼을 눌렀을 때 작동할 이벤트
        submitBtn.addEventListener('click', () => {
            if (selectedRating > 0) {
                alert(`정숙님에게 별 ${selectedRating}점을 제출했습니다! 💘`);
            }
        });
    }
    const replyText = document.getElementById('reply_text');
    const submitReplyBtn = document.getElementById('submit_reply');
    if (replyText && submitReplyBtn) {
        submitReplyBtn.addEventListener('click', () => {
            const commentValue = replyText.value.trim();
            if (commentValue === "") {
                alert("댓글 내용을 입력해 주세요! ✍️");
                return;
            }
            const nicknameElement = document.querySelector('.profile_info .nickname');
            const imgElement = document.querySelector('main img');

            const targetNickname = nicknameElement ? nicknameElement.innerText : "알 수 없음";
            const targetImgSrc = imgElement ? imgElement.getAttribute('src') : "";

            const historyLog = {
                nickname: targetNickname,
                image: targetImgSrc,
                rating: selectedRating, 
                comment: commentValue,
                date: new Date().toLocaleString() 
            };
            // 브라우저 콘솔 확인용
            console.log("마이페이지로 보낼 저장 데이터:", historyLog);
            // 알림창 띄우기
            alert("댓글이 제출되었습니다. 비매너 댓글 작성 시, 운영진에 의해 패널티가 부여됩니다.");
            // 제출 완료 후 입력창 청소
            replyText.value = "";
        });
    }
    // 좋아요 버튼 누를 때 닉네임을 주소창에 달고 이동하는 함수 추가 연동
    const likeBtn = document.getElementById('like');
    if (likeBtn) {
        likeBtn.onclick = function() {
            const nicknameElement = document.querySelector('.profile_info .nickname');
            const nickname = nicknameElement ? nicknameElement.innerText.trim() : "○○";            
            // 주소 뒤에 name=정숙 값을 붙여서 like.html 페이지로 토스!
            location.href = "https://jack.dothome.co.kr/UAS/like.html?name=" + encodeURIComponent(nickname);
        };
    }
});